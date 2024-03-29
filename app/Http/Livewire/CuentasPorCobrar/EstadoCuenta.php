<?php

namespace App\Http\Livewire\CuentasPorCobrar;

use Livewire\Component;
use App\Models\Cliente;
use App\Models\Cargo;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CargosExport;
use Carbon\Carbon;

class EstadoCuenta extends Component
{
    public $clientes, $idCliente, $fechaInicio, $fechaFinal,$estadoCuentas;
    // protected $estadoCuenta = null;

    public function mount(){
        $this->clientes = Cliente::all()->sortBy('razonSocial');
        $fechaActual = Carbon::now();
        
        $this->fechaInicio = Carbon::parse($fechaActual)->format("Y-m-d");
        $this->fechaFinal = Carbon::parse($fechaActual)->format("Y-m-d");
        // $this->estadoCuenta = collect();
    }

    public function render()
    {
        // $estCuentas = $this->estadoCuenta;
        return view('livewire.cuentas-por-cobrar.estado-cuenta');
        
    }

    public function buscar(){
        $this->estadoCuentas = NULL;
        // dd($this->estadoCuenta);
        $this->estadoCuentas = Cargo::where('idCliente', $this->idCliente)
                                    ->where('idEstado',1)
                                    ->where('saldo','>',0)
                                    ->whereBetween('fechaEmision', [$this->fechaInicio, $this->fechaFinal])
                                    ->orderBy('fechaEmision', 'asc')
                                    ->get();
        // dd($this->estadoCuentas);      
    }

    public function exportar(){
        return Excel::download(new CargosExport($this->idCliente,$this->fechaInicio,$this->fechaFinal),'Estado-de-cuentas.xlsx');
    }
}
