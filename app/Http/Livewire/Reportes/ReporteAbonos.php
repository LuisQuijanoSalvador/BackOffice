<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\VistaReporteAbono; // Tu modelo de la vista
use Carbon\Carbon; // Asegúrate de importar Carbon
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbonosReporteExport; // Tu clase de exportación

class ReporteAbonos extends Component
{
    use WithPagination;

    public $fechaInicio;
    public $fechaFin;
    public $sortBy = 'fechaAbono';
    public $sortDirection = 'desc';

    protected $queryString = ['fechaInicio', 'fechaFin', 'sortBy', 'sortDirection']; // Para mantener los filtros en la URL

    protected $rules = [
        'fechaInicio' => 'required|date',
        'fechaFin' => 'required|date|after_or_equal:fechaInicio',
    ];

    protected $messages = [
        'fechaInicio.required' => 'La fecha de inicio es obligatoria.',
        'fechaInicio.date' => 'La fecha de inicio no tiene un formato válido.',
        'fechaFin.required' => 'La fecha fin es obligatoria.',
        'fechaFin.date' => 'La fecha fin no tiene un formato válido.',
        'fechaFin.after_or_equal' => 'La fecha fin debe ser igual o posterior a la fecha de inicio.',
    ];

    public function mount()
    {
        // Establecer fechas por defecto si no están en la URL
        if (!$this->fechaInicio) {
            $this->fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$this->fechaFin) {
            $this->fechaFin = Carbon::now()->endOfMonth()->format('Y-m-d');
        }
    }

    public function updated($propertyName)
    {
        // Resetear la paginación cuando cambian las fechas
        if (in_array($propertyName, ['fechaInicio', 'fechaFin'])) {
            $this->resetPage();
            $this->validate(); // Validar al cambiar las fechas
        }
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortBy = $field;
    }

    public function generarReporte()
    {
        $this->validate(); // Valida antes de generar el reporte explícitamente
        // La tabla se refresca automáticamente debido a wire:model.live y mount/updated
        session()->flash('success', 'Reporte generado exitosamente.');
    }

    public function exportarExcel()
    {
        $this->validate(); // Valida el rango de fechas antes de exportar

        $query = VistaReporteAbono::query()
                    ->whereBetween('fechaAbono', [$this->fechaInicio, $this->fechaFin]);

        $abonosData = $query->orderBy($this->sortBy, $this->sortDirection)->get();

        if ($abonosData->isEmpty()) {
            session()->flash('info', 'No hay datos para exportar a Excel en el rango de fechas seleccionado.');
            return;
        }

        $fileName = 'Reporte_Abonos_' . $this->fechaInicio . '_a_' . $this->fechaFin . '.xlsx';
        return Excel::download(new AbonosReporteExport($abonosData), $fileName);
    }

    public function render()
    {
        // Validar primero para mostrar errores antes de la consulta
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // No hacemos nada, solo evitamos que se ejecute la consulta con fechas inválidas
            // Los errores se mostrarán en la vista automáticamente
            $abonos = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage()
                ? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10)
                : collect()->paginate(10); // Devuelve una paginación vacía
            return view('livewire.reportes.reporte-abonos', [
                'abonos' => $abonos,
            ]);
        }

        $abonos = VistaReporteAbono::query()
                    ->whereBetween('fechaAbono', [$this->fechaInicio, $this->fechaFin])
                    ->orderBy($this->sortBy, $this->sortDirection)
                    ->paginate(10); // Paginación

        return view('livewire.reportes.reporte-abonos', [
            'abonos' => $abonos,
        ]);
    }
}