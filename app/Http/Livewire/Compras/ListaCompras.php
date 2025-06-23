<?php

namespace App\Http\Livewire\Compras;

use Livewire\Component;
use App\Models\Supplier;
use Carbon\Carbon;
use App\Models\Compra;
use App\Models\Estado;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class ListaCompras extends Component
{
    use WithPagination;
    
    public $fechaInicio, $fechaFin, $filtroProveedor, $filtroDocumento;
    public $filtro = 'fechas';
    public $search = '';
    public $perPage = 10;
    // Propiedades para el modal de confirmación de anulación/eliminación
    public $showConfirmModal = false;
    public $compraIdToDelete = null;
    public $actionType = ''; // 'anular' o 'eliminar'

    // Livewire 2.x - Paginación por defecto de Bootstrap
    protected $paginationTheme = 'bootstrap';

    public function mount(){
        $this->proveedores = Supplier::all()->sortBy('razonSocial');
        $fechaActual = Carbon::now();
        
        $this->fechaInicio = Carbon::parse($fechaActual)->format("Y-m-d");
        $this->fechaFin = Carbon::parse($fechaActual)->format("Y-m-d");
    }

    // Método para mostrar el modal de confirmación
    public function confirmAction($compraId, $action)
    {
        $this->compraIdToDelete = $compraId;
        $this->actionType = $action;
        $this->showConfirmModal = true;
        $this->emit('show-confirm-modal');
    }

    // Método para ocultar el modal de confirmación
    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->reset(['compraIdToDelete', 'actionType']);
        $this->emit('hide-confirm-modal');
    }

    // Método para anular una compra
    public function anularCompra()
    {
        $compra = Compra::find($this->compraIdToDelete);

        if ($compra) {
            // Asegúrate de que tienes un estado 'Anulado' en tu tabla 'estados'
            $estadoAnulado = Estado::where('descripcion', 'Anulado')->first();

            if ($estadoAnulado) {
                $compra->estado = $estadoAnulado->id;
                $compra->save();
                session()->flash('message', 'Compra anulada exitosamente.');
            } else {
                session()->flash('error', 'Estado "Anulado" no encontrado en la base de datos.');
            }
        } else {
            session()->flash('error', 'Compra no encontrada.');
        }

        $this->closeConfirmModal();
    }

    // Método para eliminar una compra
    public function deleteCompra()
    {
        $compra = Compra::find($this->compraIdToDelete);

        if ($compra) {
            // Esto eliminará la compra y sus detalles relacionados debido a onDelete('cascade')
            // en la migración de compra_detalles.
            $compra->delete();
            session()->flash('message', 'Compra eliminada permanentemente.');
        } else {
            session()->flash('error', 'Compra no encontrada.');
        }

        $this->closeConfirmModal();
    }
    
    public function render()
    {
        // Cargar las compras con las relaciones necesarias (proveedor, tipoDocumento, estado)
        $compras = Compra::with(['proveedor', 'tipoDocumentoR', 'estadoR'])
                         ->when($this->search, function ($query) {
                             $query->where('numero', 'like', '%' . $this->search . '%')
                                   ->orWhere('serie', 'like', '%' . $this->search . '%')
                                   // Puedes añadir más campos para la búsqueda, por ejemplo:
                                   ->orWhereHas('proveedor', function ($q) {
                                       $q->where('razonSocial', 'like', '%' . $this->search . '%');
                                   })
                                    // **NUEVO: Búsqueda en campo concatenado 'serie-numero'**
                                    ->orWhere(DB::raw("CONCAT(serie, '-', numero)"), 'like', '%' . $this->search . '%');
                         })
                         ->orderBy('fechaEmision', 'desc') // Ordenar por fecha de emisión descendente
                         ->paginate($this->perPage);

        return view('livewire.compras.lista-compras', [
            'compras' => $compras,
        ]);
    }
}
