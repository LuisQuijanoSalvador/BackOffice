<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cargo;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CuentasPorCobrarExport;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

class CuentasPorCobrarReport extends Component
{
    use WithPagination;

    public $fechaDesde;
    public $fechaHasta;
    public $clienteId = '';
    public $moneda = 'USD';
    public $tipoCliente = 'todos';
    public $search = '';
    public $estado = 1; // 1 = Activo

    protected $queryString = [
        'fechaDesde' => ['except' => ''],
        'fechaHasta' => ['except' => ''],
        'clienteId' => ['except' => ''],
        'moneda' => ['except' => 'USD'],
        'tipoCliente' => ['except' => 'todos'],
    ];

    public function mount()
    {
        $this->fechaDesde = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fechaHasta = Carbon::now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingClienteId()
    {
        $this->resetPage();
    }

    public function render()
    {
        $cargosAgrupados = $this->getCargosAgrupadosPorCliente();

        $resumen = $this->calcularResumen($cargosAgrupados);

        $chartData = $this->prepararDatosGrafico($cargosAgrupados);

        return view('livewire.cuentas-por-cobrar-report', [
            'cargosAgrupados' => $cargosAgrupados,
            'resumen' => $resumen,
            'chartData' => $chartData,
            'clientes' => Cliente::where('estado', 1)
                ->orderBy('razonSocial')
                ->get()
        ]);
    }

    private function getCargosAgrupadosPorCliente()
    {
        $query = DB::table('cargos')
            ->select(
                'cargos.idCliente',
                'clientes.razonSocial',
                'clientes.numeroDocumentoIdentidad',
                'clientes.tipoCliente',
                DB::raw('SUM(cargos.saldo) as monto_total'),
                DB::raw('SUM(CASE WHEN cargos.fechaVencimiento < CURDATE() AND cargos.saldo > 0 THEN cargos.saldo ELSE 0 END) as deuda_vencida'),
                DB::raw('MAX(CASE WHEN cargos.fechaVencimiento < CURDATE() AND cargos.saldo > 0 THEN DATEDIFF(CURDATE(), cargos.fechaVencimiento) ELSE 0 END) as max_dias_atraso'),
                DB::raw('COUNT(*) as total_cargos')
            )
            ->join('clientes', 'cargos.idCliente', '=', 'clientes.id')
            ->where('cargos.idEstado', $this->estado)
            ->where('cargos.saldo', '>', 0)
            ->whereBetween('cargos.fechaEmision', [$this->fechaDesde, $this->fechaHasta]);

        if ($this->moneda) {
            $query->where('cargos.moneda', $this->moneda);
        }

        if ($this->clienteId) {
            $query->where('cargos.idCliente', $this->clienteId);
        }

        if ($this->tipoCliente !== 'todos') {
            $tipoClienteNum = $this->tipoCliente === 'corporativo' ? 1 : 2;
            $query->where('clientes.tipoCliente', $tipoClienteNum);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('clientes.razonSocial', 'like', "%{$this->search}%")
                    ->orWhere('clientes.numeroDocumentoIdentidad', 'like', "%{$this->search}%");
            });
        }

        $resultados = $query
            ->groupBy('cargos.idCliente', 'clientes.razonSocial', 'clientes.numeroDocumentoIdentidad', 'clientes.tipoCliente')
            ->orderBy('deuda_vencida', 'desc')
            ->paginate(20);

        return $resultados;
    }

    private function getCargosAgrupadosParaExportar()
    {
        $query = DB::table('cargos')
            ->select(
                'cargos.idCliente',
                'clientes.razonSocial',
                'clientes.numeroDocumentoIdentidad',
                DB::raw('SUM(cargos.saldo) as monto_total'),
                DB::raw('SUM(CASE WHEN cargos.fechaVencimiento < CURDATE() AND cargos.saldo > 0 THEN cargos.saldo ELSE 0 END) as deuda_vencida'),
                DB::raw('MAX(CASE WHEN cargos.fechaVencimiento < CURDATE() AND cargos.saldo > 0 THEN DATEDIFF(CURDATE(), cargos.fechaVencimiento) ELSE 0 END) as max_dias_atraso'),
                DB::raw('COUNT(*) as total_cargos')
            )
            ->join('clientes', 'cargos.idCliente', '=', 'clientes.id')
            ->where('cargos.idEstado', $this->estado)
            ->where('cargos.saldo', '>', 0)
            ->whereBetween('cargos.fechaEmision', [$this->fechaDesde, $this->fechaHasta]);

        if ($this->moneda) {
            $query->where('cargos.moneda', $this->moneda);
        }

        if ($this->clienteId) {
            $query->where('cargos.idCliente', $this->clienteId);
        }

        if ($this->tipoCliente !== 'todos') {
            $tipoClienteNum = $this->tipoCliente === 'corporativo' ? 1 : 2;
            $query->where('clientes.tipoCliente', $tipoClienteNum);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('clientes.razonSocial', 'like', "%{$this->search}%")
                    ->orWhere('clientes.numeroDocumentoIdentidad', 'like', "%{$this->search}%");
            });
        }

        // AQUÍ ESTÁ LA DIFERENCIA: Usamos ->get() en lugar de ->paginate()
        return $query
            ->groupBy('cargos.idCliente', 'clientes.razonSocial', 'clientes.numeroDocumentoIdentidad')
            ->orderBy('deuda_vencida', 'desc')
            ->get();
    }

    private function calcularResumen($cargosAgrupados)
    {
        $totalMonto = 0;
        $totalDeudaVencida = 0;
        $totalClientes = 0;
        $totalCargos = 0;

        foreach ($cargosAgrupados as $cargo) {
            $totalMonto += $cargo->monto_total;
            $totalDeudaVencida += $cargo->deuda_vencida;
            $totalClientes++;
            $totalCargos += $cargo->total_cargos;
        }

        return [
            'totalMonto' => $totalMonto,
            'totalDeudaVencida' => $totalDeudaVencida,
            'totalClientes' => $totalClientes,
            'totalCargos' => $totalCargos
        ];
    }

    private function prepararDatosGrafico($cargosAgrupados)
    {
        // Verificar si es un paginador y obtener los items
        $items = [];

        if ($cargosAgrupados instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $items = $cargosAgrupados->items();
        } elseif ($cargosAgrupados instanceof \Illuminate\Support\Collection) {
            $items = $cargosAgrupados->all();
        } else {
            // Si es un array u otro tipo
            $items = (array) $cargosAgrupados;
        }

        // Convertir a colección y tomar los primeros 10
        $top10 = collect($items)->take(10);

        // Extraer los datos
        $labels = $top10->pluck('razonSocial')->toArray();
        $montos = $top10->pluck('monto_total')->map(function ($val) {
            return (float)$val;
        })->toArray();
        $vencidos = $top10->pluck('deuda_vencida')->map(function ($val) {
            return (float)$val;
        })->toArray();

        return [
            'labels' => $labels,
            'montos' => $montos,
            'vencidos' => $vencidos,
        ];
    }

    public function exportarExcel()
    {
        // $cargosAgrupados = $this->getCargosAgrupadosPorCliente();
        // Usamos el mismo método para obtener todos los registros sin paginar
        $cargosAgrupados = $this->getCargosAgrupadosParaExportar();

        return Excel::download(
            new CuentasPorCobrarExport($cargosAgrupados, $this->moneda),
            'cuentas_por_cobrar_' . $this->moneda . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportarPDF()
    {
        // 1. Usamos el nuevo método que devuelve una Colección (->get())
        $cargosAgrupados = $this->getCargosAgrupadosParaExportar();

        // 2. Ahora SÍ funcionarán sum() y count() porque es una Colección
        $resumen = [
            'totalMonto' => $cargosAgrupados->sum('monto_total'),
            'totalDeudaVencida' => $cargosAgrupados->sum('deuda_vencida'),
            'totalClientes' => $cargosAgrupados->count(),
            'totalCargos' => $cargosAgrupados->sum('total_cargos')
        ];

        $pdf = Pdf::loadView('livewire.pdf.cuentas-por-cobrar', [
            'cargosAgrupados' => $cargosAgrupados,
            'resumen' => $resumen,
            'fechaDesde' => $this->fechaDesde,
            'fechaHasta' => $this->fechaHasta,
            'moneda' => $this->moneda
        ]);

        $pdf->setPaper('letter', 'landscape');

        // 3. streamDownload para Livewire 2
        return Response::streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'cuentas_por_cobrar_' . $this->moneda . '_' . now()->format('Y-m-d') . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function limpiarFiltros()
    {
        $this->reset(['clienteId', 'search', 'tipoCliente']);
        $this->fechaDesde = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fechaHasta = Carbon::now()->format('Y-m-d');
        $this->resetPage();
    }
}
