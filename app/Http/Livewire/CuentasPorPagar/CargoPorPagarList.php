<?php

namespace App\Http\Livewire\CuentasPorPagar;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CargoPorPagar;
use Carbon\Carbon;

class CargoPorPagarList extends Component
{
    use WithPagination;

    public $search = '';
    public $fechaInicio;
    public $fechaFin;
    
    // Propiedades para selección múltiple
    public $selectedCargos = [];
    public $selectAll = false;

    protected $queryString = ['search', 'fechaInicio', 'fechaFin'];

    public function mount()
    {
        // Por defecto, mostrar los cargos del último mes para no saturar la vista
        $this->fechaInicio = Carbon::now()->subMonth()->format('Y-m-d');
        $this->fechaFin = Carbon::now()->format('Y-m-d');
    }

        public function updatedSelectAll($value)
    {
        if ($value) {
            // Consultamos los IDs directamente usando los filtros actuales
            $this->selectedCargos = CargoPorPagar::query()
                ->where('saldo', '>', 0)
                ->whereBetween('fechaEmision', [$this->fechaInicio, $this->fechaFin])
                ->when($this->search, function ($query) {
                    $query->whereHas('proveedor', function ($q) {
                        $q->where('razonSocial', 'like', '%' . $this->search . '%')
                          ->orWhere('numeroDocumentoIdentidad', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('serieDocumento', 'like', '%' . $this->search . '%')
                    ->orWhere('numeroDocumento', 'like', '%' . $this->search . '%');
                })
                ->pluck('id')
                ->map(fn($id) => (string)$id) // Livewire 2 requiere que los IDs de checkboxes sean strings
                ->toArray();
        } else {
            $this->selectedCargos = [];
        }
    }

    public function updatedSelectedCargos()
    {
        // Contamos cuántos registros coinciden con los filtros actuales
        $totalFiltrados = CargoPorPagar::query()
            ->where('saldo', '>', 0)
            ->whereBetween('fechaEmision', [$this->fechaInicio, $this->fechaFin])
            ->when($this->search, function ($query) {
                $query->whereHas('proveedor', function ($q) {
                    $q->where('razonSocial', 'like', '%' . $this->search . '%')
                      ->orWhere('numeroDocumentoIdentidad', 'like', '%' . $this->search . '%');
                })
                ->orWhere('serieDocumento', 'like', '%' . $this->search . '%')
                ->orWhere('numeroDocumento', 'like', '%' . $this->search . '%');
            })
            ->count();

        // Si la cantidad de seleccionados es igual al total filtrado, marcamos "Seleccionar Todos"
        $this->selectAll = count($this->selectedCargos) === $totalFiltrados;
    }

    public function updatingSearch() { $this->resetPage(); }

    public function render()
    {
        $cargos = CargoPorPagar::with(['proveedor', 'documento'])
            ->where('saldo', '>', 0) // Solo mostrar cargos con saldo pendiente
            ->whereBetween('fechaEmision', [$this->fechaInicio, $this->fechaFin])
            ->when($this->search, function ($query) {
                $query->whereHas('proveedor', function ($q) {
                    $q->where('razonSocial', 'like', '%' . $this->search . '%')
                      ->orWhere('numeroDocumentoIdentidad', 'like', '%' . $this->search . '%');
                })
                ->orWhere('serieDocumento', 'like', '%' . $this->search . '%')
                ->orWhere('numeroDocumento', 'like', '%' . $this->search . '%');
            })
            ->orderBy('fechaVencimiento', 'asc')
            ->paginate(15);

        return view('livewire.cuentas-por-pagar.cargo-por-pagar-list', compact('cargos'));
    }
}