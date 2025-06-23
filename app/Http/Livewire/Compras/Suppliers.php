<?php

namespace App\Http\Livewire\Compras;

use Livewire\Component;
use App\Models\TipoDocumentoIdentidad;
use App\Models\Estado;
use Livewire\WithPagination;
use App\Models\Supplier;

class Suppliers extends Component
{
    use WithPagination;

    public $search = "";
    public $sort= 'razonSocial';
    public $direction = 'asc';

    public $idRegistro, $razonSocial, $direccionFiscal,$tipoDocumentoIdentidad, $numeroDocumentoIdentidad,
            $numeroTelefono, $correo, $estado;

    public function rules(){
        return[
            'razonSocial'               =>   'required',
            'direccionFiscal'           =>   'required',
            'tipoDocumentoIdentidad'    =>   'required',
            'numeroDocumentoIdentidad'  =>   'required',
            'correo'                    =>   'nullable|email',
            'estado'                    =>   'required',
        ];
    }

    protected $messages = [
        'razonSocial.required'               =>   'Este campo es requerido',
        'direccionFiscal.required'           =>   'Este campo es requerido',
        'tipoDocumentoIdentidad.required'    =>   'Debe seleccionar una opción',
        'correo.email'                       =>   'Este campo no tiene el formato correcto',
        'estado.required'                    =>   'Debe seleccionar una opción',
    ];
    
    public function render()
    {
        $proveedors = Supplier::where('razonSocial', 'like', "%$this->search%")
                            ->orderBy($this->sort, $this->direction)
                            ->paginate(6);
        $tipoDocumentoIdentidads = TipoDocumentoIdentidad::all()->sortBy('descripcion');
        $estados = Estado::all()->sortBy('descripcion');
        return view('livewire.compras.suppliers',compact('proveedors','tipoDocumentoIdentidads','estados'));
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

        $proveedor = new Supplier();
        $proveedor->razonSocial = $this->razonSocial;
        $proveedor->direccionFiscal = $this->direccionFiscal;
        $proveedor->tipoDocumentoIdentidad = $this->tipoDocumentoIdentidad;
        $proveedor->numeroDocumentoIdentidad = $this->numeroDocumentoIdentidad;
        $proveedor->numeroTelefono = $this->numeroTelefono;
        $proveedor->correo = $this->correo;
        $proveedor->estado = $this->estado;
        $proveedor->usuarioCreacion = auth()->user()->id;
        $proveedor->save();
        $this->limpiarControles();
        session()->flash('success', 'Los datos se han guardado exitosamente.');
    }

    public function limpiarControles(){
        $this->idRegistro = 0;
        $this->razonSocial = '';
        $this->direccionFiscal = '';
        $this->tipoDocumentoIdentidad = '';
        $this->numeroDocumentoIdentidad = '';
        $this->numeroTelefono = '';
        $this->correo = '';
        $this->estado = '';
    }

    public function editar($id){
        $proveedor = Supplier::find($id);
        $this->limpiarControles();
        $this->idRegistro = $proveedor->id;
        $this->razonSocial = $proveedor->razonSocial;
        $this->direccionFiscal = $proveedor->direccionFiscal;
        $this->tipoDocumentoIdentidad = $proveedor->tipoDocumentoIdentidad;
        $this->numeroDocumentoIdentidad = $proveedor->numeroDocumentoIdentidad;
        $this->numeroTelefono = $proveedor->numeroTelefono;
        $this->correo = $proveedor->correo;
        $this->estado = $proveedor->estado;
    }

    public function actualizar($id){
        $proveedor = Supplier::find($id);
        $proveedor->razonSocial = $this->razonSocial;
        $proveedor->direccionFiscal = $this->direccionFiscal;
        $proveedor->tipoDocumentoIdentidad = $this->tipoDocumentoIdentidad;
        $proveedor->numeroDocumentoIdentidad = $this->numeroDocumentoIdentidad;
        $proveedor->numeroTelefono = $this->numeroTelefono;
        $proveedor->correo = $this->correo;
        $proveedor->estado = $this->estado;
        $proveedor->usuarioModificacion = auth()->user()->id;
        $proveedor->save();
        $this->limpiarControles();
        session()->flash('success', 'Los datos se han actualizado exitosamente.');
    }

    public function encontrar($id){
        $proveedor = Supplier::find($id);
        $this->idRegistro = $proveedor->id;
        $this->razonSocial = $proveedor->razonSocial;
    }

    public function eliminar($id){
        $proveedor = Supplier::find($id);
        $proveedor->delete();
        $this->limpiarControles();
    }
}
