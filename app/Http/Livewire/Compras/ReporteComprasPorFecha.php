<?php

namespace App\Http\Livewire\Compras;

use Livewire\Component;
use App\Models\Compra;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; 
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\ComprasFechaExport; 
use Illuminate\Support\Facades\Log;

class ReporteComprasPorFecha extends Component
{
    public $fechaInicio;
    public $fechaFin;
    public $compras = [];
    public $mostrarResultados = false;

    protected $rules = [
        'fechaInicio' => 'required|date',
        'fechaFin'    => 'required|date|after_or_equal:fechaInicio',
    ];

    protected $messages = [
        'fechaInicio.required'      => 'La fecha de inicio es obligatoria.',
        'fechaInicio.date'          => 'La fecha de inicio no tiene un formato válido.',
        'fechaFin.required'         => 'La fecha fin es obligatoria.',
        'fechaFin.date'             => 'La fecha fin no tiene un formato válido.',
        'fechaFin.after_or_equal'   => 'La fecha fin debe ser igual o posterior a la fecha de inicio.',
    ];

    public function mount()
    {
        // Inicializa las fechas con el primer y último día del mes actual por defecto
        $this->fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = Carbon::now()->endOfMonth()->format('Y-m-d');
        // No cargar resultados al inicio, solo cuando el usuario lo solicite
        $this->compras = [];
        $this->mostrarResultados = false;
    }

    public function generarReporte()
    {
        $this->validate();

        try {
            $this->compras = Compra::with('proveedor', 'tipoDocumentoR', 'estadoR') // Carga relaciones
                                    ->whereBetween('fechaEmision', [$this->fechaInicio, $this->fechaFin])
                                    ->orderBy('fechaEmision', 'asc')
                                    ->get();

            $this->mostrarResultados = true;

            if ($this->compras->isEmpty()) {
                session()->flash('info', 'No se encontraron compras en el rango de fechas seleccionado.');
            } else {
                session()->flash('success', 'Reporte generado exitosamente.');
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error al generar el reporte: ' . $e->getMessage());
            Log::error('Error en ReporteComprasPorFecha: ' . $e->getMessage());
        }
    }

    public function exportarExcel()
    {
        $this->validate(); // Valida las fechas antes de exportar

        if (empty($this->compras)) {
            // Asegurarse de que los datos estén cargados si no se llamó a generarReporte antes
            $this->generarReporte();
        }

        if ($this->compras->isEmpty()) {
            session()->flash('info', 'No hay datos para exportar a Excel. Genere el reporte primero.');
            return;
        }

        $fileName = 'Reporte_Compras_Fechas_' . $this->fechaInicio . '_a_' . $this->fechaFin . '.xlsx';

        // Usa la clase de exportación que crearemos en el siguiente paso
        return Excel::download(new ComprasFechaExport($this->compras), $fileName);
    }

    public function render()
    {
        return view('livewire.compras.reporte-compras-por-fecha');
    }
}