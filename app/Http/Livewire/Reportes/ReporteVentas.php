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
        $query = DB::table('vista_ventas');

        if($this->fechaInicio and $this->fechaFin and $this->idCliente){
            $query->where('idCliente',$this->idCliente)
                   ->whereBetween('FechaEmision',[$this->fechaInicio, $this->fechaFin]);
        }
        if($this->fechaInicio and $this->fechaFin and !$this->idCliente){
            $query->whereBetween('FechaEmision',[$this->fechaInicio, $this->fechaFin]); 
        }
        $datos = $query
                ->orderBy('FechaEmision')
                ->orderBy('pasajero')
                ->orderBy('tipo')
                ->paginate(10);
        
        // return view('livewire.reportes.reporte-ventas',compact('clientes'));
        return view('livewire.reportes.reporte-ventas',['datos' => $datos, 'clientes' => $clientes]);
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
