<?php

namespace App\Http\Livewire\Contabilidad;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CuentaContable;
use App\Models\Estado;

class CuentaContableManagement extends Component
{
    use WithPagination;
    public $deleteId;
    public $cuentaId;
    public $cuenta;
    public $descripcion;
    public $search = '';
    
    // Control del Modal
    public $isOpen = false;

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Abrir modal para crear
    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    // Abrir modal para editar
    public function edit($id)
    {
        $cuenta = CuentaContable::findOrFail($id);
        $this->cuentaId = $id;
        $this->cuenta = $cuenta->cuenta;
        $this->descripcion = $cuenta->descripcion;
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
        // Disparamos el evento para que JS abra el modal de Bootstrap
        $this->dispatchBrowserEvent('open-modal'); 
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->dispatchBrowserEvent('close-modal');
    }

    private function resetInputFields()
    {
        $this->cuentaId = null;
        $this->cuenta = '';
        $this->descripcion = '';
    }

    public function store()
    {
        $this->validate([
            'cuenta' => 'required|string|max:20|unique:cuenta_contables,cuenta,' . $this->cuentaId,
            'descripcion' => 'required|string|max:255',
        ]);

        $estadoActivoId = Estado::where('descripcion', 'Activo')->value('id');

        CuentaContable::updateOrCreate(
            ['id' => $this->cuentaId],
            [
                'cuenta' => $this->cuenta,
                'descripcion' => $this->descripcion,
                'usuarioCreacion' => $this->cuentaId ? auth()->id() : auth()->id(), // Se mantiene o actualiza
                'usuarioModificacion' => $this->cuentaId ? auth()->id() : null,
                'idEstado' => $estadoActivoId,
            ]
        );

        session()->flash('message', $this->cuentaId ? 'Cuenta actualizada exitosamente.' : 'Cuenta creada exitosamente.');
        $this->closeModal();
        $this->resetInputFields();
    }
    // Método para abrir el modal de confirmación
    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->dispatchBrowserEvent('open-delete-modal');
    }

    // Método para cancelar y cerrar el modal
    public function cancelDelete()
    {
        $this->deleteId = null;
        $this->dispatchBrowserEvent('close-delete-modal');
    }

    public function delete()
    {
        if ($this->deleteId) {
            CuentaContable::find($this->deleteId)->delete();
            session()->flash('message', 'Cuenta eliminada exitosamente.');
            
            $this->deleteId = null;
            $this->dispatchBrowserEvent('close-delete-modal');
        }
    }

    public function render()
    {
        $cuentas = CuentaContable::with('estado', 'userCreacion')
            ->where('cuenta', 'like', '%' . $this->search . '%')
            ->orWhere('descripcion', 'like', '%' . $this->search . '%')
            ->orderBy('cuenta', 'asc')
            ->paginate(15);

        return view('livewire.contabilidad.cuenta-contable-management', compact('cuentas'));
    }
}