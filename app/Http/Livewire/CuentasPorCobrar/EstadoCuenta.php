<?php

namespace App\Http\Livewire\CuentasPorCobrar;

use Livewire\Component;
use App\Models\Cliente;
use App\Models\Cargo;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CargosExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EstadoCuenta extends Component
{
    public $clientes, $idCliente = 0, $fechaInicio, $fechaFinal,$estadoCuentas;
    // protected $estadoCuenta = null;

    public function mount(){
        $this->clientes = Cliente::all()->sortBy('razonSocial');
        $fechaActual = Carbon::now();
        
        $this->fechaInicio = Carbon::parse($fechaActual)->format("Y-m-d");
        $this->fechaFinal = Carbon::parse($fechaActual)->format("Y-m-d");
        // $this->estadoCuenta = collect();
    }
    
    public function updatedidCliente($value)
    {
        // $value contiene el ID o valor seleccionado (ej: "1" o "2")
        // dd("El usuario seleccionó: " . $value);
        $this->estadoCuentas = NULL;
    }
    public function render()
    {
        // $estCuentas = $this->estadoCuenta;
        
        return view('livewire.cuentas-por-cobrar.estado-cuenta');
    }

    public function buscar(){    
        $cliente = Cliente::find($this->idCliente);
        if($cliente){
            if($cliente->tipoFacturacion == 1){
                $this->estadoCuentas = DB::table('vista_estadocuenta')
                                ->where('idCliente', $this->idCliente)
                                ->whereBetween('fechaEmision',[$this->fechaInicio, $this->fechaFinal])
                                ->get();
            }else{
                $this->estadoCuentas = DB::table('vista_estadocuenta_acumulado')
                                ->where('idCliente', $this->idCliente)
                                ->whereBetween('fechaEmision',[$this->fechaInicio, $this->fechaFinal])
                                ->get();
            }
        }else{
            session()->flash('error', 'Seleccione un cliente');
        }                            
    }

    public function exportar(){
        $this->estadoCuentas = NULL;
        return Excel::download(new CargosExport($this->idCliente,$this->fechaInicio,$this->fechaFinal),'Estado-de-cuentas.xlsx');
    }
}
