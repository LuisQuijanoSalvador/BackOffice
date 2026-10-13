<?php

namespace App\Http\Livewire\Contabilidad;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Subdiario;
use App\Models\Estado;

class SubdiarioManagement extends Component
{
    use WithPagination;

    public $subdiarioId;
    public $codigo;
    public $descripcion;
    public $search = '';
    public $isOpen = false;
    public $deleteId;

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function edit($id)
    {
        $subdiario = Subdiario::findOrFail($id);
        $this->subdiarioId = $id;
        $this->codigo = $subdiario->codigo;
        $this->descripcion = $subdiario->descripcion;
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
        $this->dispatchBrowserEvent('open-modal');
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->dispatchBrowserEvent('close-modal');
    }

    private function resetInputFields()
    {
        $this->subdiarioId = null;
        $this->codigo = '';
        $this->descripcion = '';
    }

    public function store()
    {
        $this->validate([
            'codigo' => 'required|string|max:20|unique:subdiarios,codigo,' . $this->subdiarioId,
            'descripcion' => 'required|string|max:255',
        ]);

        $estadoActivoId = Estado::where('descripcion', 'Activo')->value('id');

        Subdiario::updateOrCreate(
            ['id' => $this->subdiarioId],
            [
                'codigo' => $this->codigo,
                'descripcion' => $this->descripcion,
                'idEstado' => $estadoActivoId,
            ]
        );

        session()->flash('message', $this->subdiarioId ? 'Subdiario actualizado exitosamente.' : 'Subdiario creado exitosamente.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->dispatchBrowserEvent('open-delete-modal');
    }

    public function cancelDelete()
    {
        $this->deleteId = null;
        $this->dispatchBrowserEvent('close-delete-modal');
    }

    public function delete()
    {
        if ($this->deleteId) {
            Subdiario::find($this->deleteId)->delete();
            session()->flash('message', 'Subdiario eliminado exitosamente.');
            $this->deleteId = null;
            $this->dispatchBrowserEvent('close-delete-modal');
        }
    }

    public function render()
    {
        $subdiarios = Subdiario::with('estado')
            ->where('codigo', 'like', '%' . $this->search . '%')
            ->orWhere('descripcion', 'like', '%' . $this->search . '%')
            ->orderBy('codigo', 'asc')
            ->paginate(15);

        return view('livewire.contabilidad.subdiario-management', compact('subdiarios'));
    }
}