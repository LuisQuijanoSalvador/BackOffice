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
    $idMedioPago = 1, $observaciones = '', $referencia = '', $totalPagos = 0;

    public $abonos,$fechaInicio,$fechaFin;

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
    }

    public function ver($numAbono){
        $abonos = Abono::where('numeroAbono',$numAbono)->get();
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
    }

    public function exportar(){
        if(!$this->fechaInicio or !$this->fechaFin){
            session()->flash('error', 'Fechas no validas');
            return;
        }
        return Excel::download(new AbonoExport($this->fechaInicio,$this->fechaFin),'Abonos.xlsx');
    }
}
