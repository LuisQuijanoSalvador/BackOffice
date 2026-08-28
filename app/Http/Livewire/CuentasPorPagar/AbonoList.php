<?php
namespace App\Http\Livewire\CuentasPorPagar;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AbonoPago;

class AbonoList extends Component
{
    use WithPagination;
    public $search = '';

    public function updatingSearch() { $this->resetPage(); }

        public function render()
    {
        $abonos = AbonoPago::with(['cargo.proveedor', 'banco', 'estado'])
            ->where(function ($query) {
                // Buscar por nombre o RUC del proveedor
                $query->whereHas('cargo.proveedor', function($q) {
                        $q->where('razonSocial', 'like', '%'.$this->search.'%')
                          ->orWhere('numeroDocumentoIdentidad', 'like', '%'.$this->search.'%');
                    })
                    // O buscar por Serie o Número de documento en la tabla de cargos
                    ->orWhereHas('cargo', function($q) {
                        $q->where('serieDocumento', 'like', '%'.$this->search.'%')
                          ->orWhere('numeroDocumento', 'like', '%'.$this->search.'%');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.cuentas-por-pagar.abono-list', compact('abonos'));
    }
}