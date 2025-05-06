<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ComiconsExport;
use Carbon\Carbon;

class ComisionConsolidador extends Component
{
    protected $comisiones;
    public $fechaInicio, $fechaFin;

    public function mount(){
        $fechaActual = Carbon::now();
        
        $this->fechaInicio = Carbon::parse($fechaActual)->format("Y-m-d");
        $this->fechaFin = Carbon::parse($fechaActual)->format("Y-m-d");
    }

    public function render()
    {
        return view('livewire.reportes.comision-consolidador');
    }

    public function filtrar(){
        // $this->margenes = DB::select('CALL get_xm_fechas(?, ?)', [$this->fechaInicio, $this->fechaFin]);
        if($this->fechaInicio and $this->fechaFin){
            $this->comisiones = DB::table('vista_comisionConsolidador')
                            ->whereBetween('FechaEmision',[$this->fechaInicio, $this->fechaFin])
                            ->orderby('fechaEmision')
                            ->get();
        }
    }

    public function exportar(){
        return Excel::download(new ComiconsExport($this->fechaInicio,$this->fechaFin),'ComisionesConsolidador.xlsx');
    }
}
