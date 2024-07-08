<?php

namespace App\Http\Livewire\Tablas;

use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BancoExport;
use App\Models\Banco;
use App\Models\Estado;

class Bancos extends Component
{
    use WithPagination;
    public $search = "";
    public $sort= 'nombre';
    public $direction = 'asc';

    public $idRegistro, $nombre, $numeroCuenta, $cci, $cuentaContable, $idEstado;

    public function rules(){
        return[
            'nombre'      => 'required',
            'numeroCuenta'     => 'required',
            'cci'     => 'required',
            'cuentaContable' => 'required',
            'idEstado'  => 'required'
        ];
    }

    protected $messages = [
        'nombre.required' => 'El campo Nombre no puede estar en blanco.',
        'numeroCuenta.required' => 'El campo Numero Cuenta no puede estar en blanco.',
        'cci.required' => 'El campo CCI no puede estar en blanco.',
        'cuentaContable.required' => 'El campo Cuenta Contable no puede estar en blanco.',
        'idEstado.required' => 'Debe seleccionar una opcion.',
    ];

    public function render()
    {
        $bancos = Banco::where('nombre', 'like', "%$this->search%")
                        ->orderBy($this->sort, $this->direction)
                        ->paginate(6);
        
        $estados = Estado::all()->sortBy('descripcion');
        return view('livewire.tablas.bancos', compact('bancos','estados'));
    }

    public function order($sort){
        if ($this->sort == $sort) {
            if ($this->direction == 'desc') {
                $this->direction = 'asc';
            } else {
                $this->direction = 'desc';
            }
        } else {
            $this->sort = $sort;
            $this->direction = 'desc';
        }
    }

    public function grabar(){
        $this->validate();

        $banco = new Banco();
        $banco->nombre  = $this->nombre;
        $banco->numeroCuenta = $this->numeroCuenta;
        $banco->cci  = $this->cci;
        $banco->cuentaContableSoles  = $this->cuentaContable;
        $banco->idEstado = $this->idEstado;
        $banco->usuarioCreacion = auth()->user()->id;
        $banco->save();
        $this->limpiarControles();
        session()->flash('success', 'Los datos se han guardado exitosamente.');
    }

    public function limpiarControles(){
        $this->idRegistro = 0;
        $this->nombre = "";
        $this->numeroCuenta = "";
        $this->cci = "";
        $this->cuentaContable = "";
        $this->estado = "";
    }

    public function editar($id){
        $banco = Banco::find($id);
        $this->limpiarControles();
        $this->idRegistro = $banco->id;
        $this->nombre = $banco->nombre;
        $this->numeroCuenta = $banco->numeroCuenta;
        $this->cci = $banco->cci;
        $this->cuentaContable = $banco->cuentaContableSoles;
        $this->idEstado = $banco->idEstado;
    }

    public function actualizar($id){
        $banco = Banco::find($id);
        $banco->nombre = $this->nombre;
        $banco->numeroCuenta = $this->numeroCuenta;
        $banco->cci = $this->cci;
        $banco->cuentaContableSoles = $this->cuentaContable;
        $banco->estado = $this->estado;
        $banco->usuarioModificacion = auth()->user()->id;
        $banco->save();
        $this->limpiarControles();
    }

    public function encontrar($id){
        $banco = Banco::find($id);
        $this->idRegistro = $banco->id;
        $this->nombre = $banco->nombre;
    }

    public function eliminar($id){
        $banco = Banco::find($id);
        $banco->delete();
        $this->limpiarControles();
    }

    public function exportar(){
        return Excel::download(new BancoExport(),'Bancos.xlsx');
    }
}
