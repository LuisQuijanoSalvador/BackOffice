<?php

namespace App\Http\Livewire\CuentasPorPagar;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CargoPorPagar;
use App\Models\Estado;

class CargoPorPagarList extends Component
{
    use WithPagination;

    public $search = '';
    public $filterEstado = '';

    protected $queryString = ['search', 'filterEstado'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterEstado()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Obtenemos el ID del estado "Pagado" dinámicamente para filtrar
        $estadoPagadoId = Estado::where('descripcion', 'Pagado')->value('id');
        $estadoPendienteId = Estado::where('descripcion', 'Pendiente')->value('id');

        $cargos = CargoPorPagar::with(['proveedor', 'documento'])
            ->when($this->search, function ($query) {
                $query->whereHas('proveedor', function ($q) {
                    $q->where('razonSocial', 'like', '%' . $this->search . '%')
                      ->orWhere('numeroDocumentoIdentidad', 'like', '%' . $this->search . '%');
                })
                ->orWhere('serieDocumento', 'like', '%' . $this->search . '%')
                ->orWhere('numeroDocumento', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterEstado, function ($query) {
                if ($this->filterEstado == 'pendiente') {
                    $query->where('saldo', '>', 0);
                } elseif ($this->filterEstado == 'pagado') {
                    $query->where('saldo', '<=', 0);
                }
            })
            ->orderBy('fechaVencimiento', 'asc')
            ->paginate(10);

        return view('livewire.cuentas-por-pagar.cargo-por-pagar-list', compact('cargos'));
    }
}