<?php

namespace App\Http\Livewire\CuentasPorCobrar;

use Livewire\Component;
use App\Models\Abono;
use App\Models\MedioPago;
use App\Models\TarjetaCredito;
use App\Models\moneda;
use App\Models\Banco;
use App\Models\Cargo;
use App\Models\Documento;
use Illuminate\Support\Facades\DB;
use App\Exports\AbonoExport;
use Maatwebsite\Excel\Facades\Excel;

class Abonos extends Component
{
    public $selectedIds, $datos, $fechaAbono, $tipoCambio, $moneda = 1, $idBanco = 2, $idTarjetaCredito = 1,
    $idMedioPago = 1, $observaciones = '', $referencia = '', $totalPagos = 0, $totalAbono, $numDoc, $lBuscar = 0;

    public $abonos,$abonosVista, $fechaInicio,$fechaFin;

    public function mount(){
        $this->poblarGrid();
    }
    public function poblarGrid(){
        $this->abonos = Abono::select('abonos.fechaAbono as FechaAbono', 'abonos.monto as Monto', 
        DB::raw("CONCAT(documentos.tipoDocumento, ' ', documentos.serie, '-', LPAD(documentos.numero, 8, '0')) as Documento"),
        'documentos.razonSocial as Cliente', 'medio_pagos.descripcion as MedioPago', 
        'abonos.referencia as Referencia', 'bancos.nombre as Banco', 'abonos.numeroCuenta', 'abonos.observaciones','abonos.numeroAbono')
        ->join('medio_pagos', 'abonos.idMedioPago', '=', 'medio_pagos.id')
        ->join('bancos', 'abonos.idBanco', '=', 'bancos.id')
        ->join('cargos', 'abonos.idCargo', '=', 'cargos.id')
        ->join('documentos', 'cargos.idDocumento', '=', 'documentos.id')
        ->where('abonos.idEstado', 1)
        ->orderBy('abonos.fechaAbono')
        ->get();
    }
    public function render()
    {
        $medioPagos = MedioPago::all()->sortBy('descripcion');
        $tarjetaCreditos = TarjetaCredito::all()->sortBy('descripcion');
        $bancos = Banco::all()->sortBy('nombre');
        $monedas = moneda::all()->sortBy('codigo');
        return view('livewire.cuentas-por-cobrar.abonos',compact('medioPagos','tarjetaCreditos','bancos',
        'monedas'));
    }
    
    public function filtrar(){
        
        if(!$this->fechaInicio or !$this->fechaFin){
            session()->flash('error', 'Fechas no validas');
            return;
        }
        $this->abonos = Abono::select('abonos.fechaAbono as FechaAbono', 'abonos.monto as Monto', 
        DB::raw("CONCAT(documentos.tipoDocumento, ' ', documentos.serie, '-', LPAD(documentos.numero, 8, '0')) as Documento"),
        'documentos.razonSocial as Cliente', 'medio_pagos.descripcion as MedioPago', 
        'abonos.referencia as Referencia', 'bancos.nombre as Banco', 'abonos.numeroCuenta', 'abonos.observaciones','abonos.numeroAbono')
        ->join('medio_pagos', 'abonos.idMedioPago', '=', 'medio_pagos.id')
        ->join('bancos', 'abonos.idBanco', '=', 'bancos.id')
        ->join('cargos', 'abonos.idCargo', '=', 'cargos.id')
        ->join('documentos', 'cargos.idDocumento', '=', 'documentos.id')
        ->where('abonos.idEstado', 1)
        ->whereBetween('abonos.fechaAbono', [$this->fechaInicio, $this->fechaFin])
        ->orderBy('abonos.fechaAbono')
        ->get();
        $this->lBuscar = 1;
    }

    public function buscarDoc(){
        $this->abonos = DB::table('vista_abonos_general')
                        ->where('Documento','like',"%$this->numDoc%")
                        ->get();
        $this->lBuscar = 2;
    }

    public function ver($numAbono){
        $this->abonosVista = DB::table('vista_abonos')
                            ->where('numeroAbono',$numAbono)
                            ->orderBy('fechaAbono')
                            ->get();
        $totalAb = 0;
        foreach($this->abonosVista as $abonoVIsta){
            $totalAb = $totalAb + $abonoVIsta->Abono;
        }
        // $this->totalAbono = round($totalAb,2);
        $this->totalAbono = number_format($totalAb, 2, '.', '');
        $abono = Abono::where('numeroAbono',$numAbono)->first();
        $this->fechaAbono = $abono->fechaAbono;
        $this->idMedioPago = $abono->idMedioPago;
        $this->referencia = $abono->referencia;
        $this->idTarjetaCredito = $abono->idTarjetaCredito;
        $this->idBanco = $abono->idBanco;
        $this->moneda = $abono->moneda;
        $this->tipoCambio = $abono->tipoCambio;
        $this->observaciones = $abono->observaciones;
        $this->poblarGrid();
    //     if($this->lBuscar == 0){
    //         $this->poblarGrid();
    //     }
    //     if($this->lBuscar == 1){
    //         $this->filtrar();
    //     }
    //     if($this->lBuscar == 0){
    //         $this->buscarDoc();
    //     }
    }

    public function limpiarAbonosVista(){
        $this->abonosVista = NULL;
        $this->poblarGrid();
    }

    public function exportar(){
        if(!$this->fechaInicio or !$this->fechaFin){
            session()->flash('error', 'Fechas no validas');
            return;
        }
        return Excel::download(new AbonoExport($this->fechaInicio,$this->fechaFin),'Abonos.xlsx');
    }
}
