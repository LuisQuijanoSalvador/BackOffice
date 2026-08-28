<?php

namespace App\Http\Livewire\CuentasPorPagar;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Egreso;
use App\Models\Banco;
use App\Models\Proveedor;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EgresosExport;
use Carbon\Carbon;

class EgresoList extends Component
{
    use WithPagination;

    public $fechaInicio = '';
    public $fechaFin = '';
    public $bancoId = '';
    public $proveedorId = '';
    public $search = '';

    protected $queryString = ['fechaInicio', 'fechaFin', 'bancoId', 'proveedorId', 'search'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFechaInicio() { $this->resetPage(); }
    public function updatingFechaFin() { $this->resetPage(); }
    public function updatingBancoId() { $this->resetPage(); }
    public function updatingProveedorId() { $this->resetPage(); }

    public function limpiarFiltros()
    {
        $this->reset(['fechaInicio', 'fechaFin', 'bancoId', 'proveedorId', 'search']);
        $this->resetPage();
    }

    public function exportarExcel()
    {
        $filters = [
            'fecha_inicio' => $this->fechaInicio ?: null,
            'fecha_fin'    => $this->fechaFin ?: null,
            'banco_id'     => $this->bancoId ?: null,
            'proveedor_id' => $this->proveedorId ?: null,
        ];

        $nombreArchivo = 'egresos_' . Carbon::now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new EgresosExport($filters), $nombreArchivo);
    }

    public function render()
    {
        $bancos = Banco::orderBy('nombre')->get();
        $proveedores = Proveedor::orderBy('razonSocial')->get();

        $egresos = Egreso::with(['banco', 'documento.proveedor', 'estado'])
            ->when($this->fechaInicio, function ($query) {
                $query->whereDate('created_at', '>=', $this->fechaInicio);
            })
            ->when($this->fechaFin, function ($query) {
                $query->whereDate('created_at', '<=', $this->fechaFin);
            })
            ->when($this->bancoId, function ($query) {
                $query->where('idBanco', $this->bancoId);
            })
            ->when($this->proveedorId, function ($query) {
                $query->whereHas('documento.proveedor', function ($q) {
                    $q->where('id', $this->proveedorId);
                });
            })
            ->when($this->search, function ($query) {
                $query->whereHas('documento.proveedor', function ($q) {
                    $q->where('razonSocial', 'like', '%' . $this->search . '%')
                      ->orWhere('numeroDocumentoIdentidad', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('documento', function ($q) {
                    $q->where('serie', 'like', '%' . $this->search . '%')
                      ->orWhere('numero', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Calcular totales para el resumen
        $totalMonto = $egresos->sum('monto');
        $totalRegistros = $egresos->total();

        return view('livewire.cuentas-por-pagar.egreso-list', compact('egresos', 'bancos', 'proveedores', 'totalMonto', 'totalRegistros'));
    }
}