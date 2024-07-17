<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use App\Models\Proveedor;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\SegmentosExport;

class RepSegmentos extends Component
{
    public $fechaInicio, $fechaFin, $idConsolidador;
    public $segmentos;

    public function mount(){
        $fechaActual = Carbon::now();
        
        $this->fechaInicio = Carbon::parse($fechaActual)->format("Y-m-d");
        $this->fechaFin = Carbon::parse($fechaActual)->format("Y-m-d");
    }
    
    public function render()
    {
        $consolidadors = Proveedor::where('esConsolidador',1)->get();
        return view('livewire.reportes.rep-segmentos',compact('consolidadors'));
    }

    public function filtrar(){
        if($this->fechaInicio and $this->fechaFin and $this->idConsolidador){
            $this->segmentos = DB::table('vista_segmentos')
                            ->where('idConsolidador',$this->idConsolidador)
                            ->whereBetween('fechaEmision',[$this->fechaInicio, $this->fechaFin])
                            ->orderby('fechaEmision')
                            ->orderby('numeroBoleto')
                            ->get();
            
        }
        if($this->fechaInicio and $this->fechaFin and !$this->idConsolidador){
            $this->segmentos = DB::table('vista_segmentos')
                            ->whereBetween('fechaEmision',[$this->fechaInicio, $this->fechaFin])
                            ->orderBy('fechaEmision')
                            ->orderby('numeroBoleto')
                            ->get();
        
        }
    }

    public function exportar(){
        $this->segmentos = NULL;
        return Excel::download(new SegmentosExport($this->fechaInicio,$this->fechaFin,$this->idConsolidador),'reporte-segmentos.xlsx');
    }
}
