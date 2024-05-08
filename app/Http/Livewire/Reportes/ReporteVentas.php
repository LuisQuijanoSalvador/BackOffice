<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use App\Models\Ventas;
use App\Models\Cliente;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\VentasExport;

class ReporteVentas extends Component
{
    public $fechaInicio, $fechaFin, $idCliente;
    public $ventas;

    public function mount(){
        $fechaActual = Carbon::now();
        
        $this->fechaInicio = Carbon::parse($fechaActual)->format("Y-m-d");
        $this->fechaFin = Carbon::parse($fechaActual)->format("Y-m-d");
    }

    public function render()
    {
        $clientes = Cliente::all()->sortBy('razonSocial');
        return view('livewire.reportes.reporte-ventas',compact('clientes'));
    }

    public function filtrar(){
        if($this->fechaInicio and $this->fechaFin and $this->idCliente){
            $this->ventas = DB::table('vista_ventas')
                            ->where('idCliente',$this->idCliente)
                            ->whereBetween('FechaEmision',[$this->fechaInicio, $this->fechaFin])
                            ->orderby('FechaEmision')
                            ->orderBy('pasajero')
                            ->orderBy('tipo')
                            ->get();
        }
        if($this->fechaInicio and $this->fechaFin and !$this->idCliente){
            $this->ventas = DB::table('vista_ventas')
                            ->whereBetween('FechaEmision',[$this->fechaInicio, $this->fechaFin])
                            ->orderBy('fechaEmision')
                            ->orderBy('pasajero')
                            ->orderBy('tipo')
                            ->get();
        }
    }

    public function exportar(){
        $this->ventas = NULL;
        return Excel::download(new VentasExport($this->fechaInicio,$this->fechaFin,$this->idCliente),'ventas.xlsx');
    }
}
