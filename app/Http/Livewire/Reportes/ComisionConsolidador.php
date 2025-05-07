<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ComiconsExport;
use Carbon\Carbon;
use App\Models\Cliente;
use App\Models\Counter;

class ComisionConsolidador extends Component
{
    protected $comisiones;
    public $fechaInicio, $fechaFin, $comis;
    public $filtro = 'fechas';
    public $clientes, $counters;
    public $filtroCliente, $filtroCounter, $filtroPasajero, $filtroBoleto, $filtroFile;

    public function mount(){
        $this->clientes = Cliente::all()->sortBy('razonSocial');
        $this->counters = Counter::all()->sortBy('nombre');
        $fechaActual = Carbon::now();
        
        $this->fechaInicio = Carbon::parse($fechaActual)->format("Y-m-d");
        $this->fechaFin = Carbon::parse($fechaActual)->format("Y-m-d");
    }

    public function render()
    {
        $this->filtrar();
        return view('livewire.reportes.comision-consolidador');
    }

    public function filtrar(){
        if($this->filtro == 'fechas'){
            if($this->fechaInicio and $this->fechaFin){
                $this->comisiones = DB::table('vista_comisionConsolidador')
                                ->whereBetween('FechaEmision',[$this->fechaInicio, $this->fechaFin])
                                ->orderby('fechaEmision')
                                ->get();
                $this->comi = $this->comisiones;
            }
        }
        if($this->filtro == 'cliente'){
            $this->comisiones = DB::table('vista_comisionConsolidador')
                            ->where('idCliente',$this->filtroCliente)
                            ->orderby('fechaEmision')
                            ->get();
            $this->comi = $this->comisiones;
        }
        if($this->filtro == 'counter'){
            $this->comisiones = DB::table('vista_comisionConsolidador')
                            ->where('idCounter',$this->filtroCounter)
                            ->orderby('fechaEmision')
                            ->get();
            $this->comi = $this->comisiones;
        }
        if($this->filtro == 'pasajero'){
            $this->comisiones = DB::table('vista_comisionConsolidador')
                            ->where('pasajero', 'like', '%' . $this->filtroPasajero . '%')
                            ->orderby('fechaEmision')
                            ->get();
            $this->comi = $this->comisiones;
        }
        if($this->filtro == 'boleto'){
            $this->comisiones = DB::table('vista_comisionConsolidador')
                            ->where('NumeroBoleto', 'like', '%' . $this->filtroBoleto . '%')
                            ->orderby('fechaEmision')
                            ->get();
            $this->comi = $this->comisiones;
        }
        if($this->filtro == 'file'){
            $this->comisiones = DB::table('vista_comisionConsolidador')
                            ->where('FILE', 'like', '%' . $this->filtroFile . '%')
                            ->orderby('fechaEmision')
                            ->get();
            $this->comi = $this->comisiones;
        }
    }

    public function exportar(){
        return Excel::download(new ComiconsExport($this->comi),'ComisionesConsolidador.xlsx');
    }
}
