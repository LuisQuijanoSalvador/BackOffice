<?php

namespace App\Http\Livewire\Gestion;

use Livewire\Component;
use App\Models\Counter;
use App\Models\Boleto;
use App\Models\Aerolinea;
use App\Models\Cliente;
use App\Models\BoletoRuta;
use App\Models\BoletoPago;
use App\Models\TarjetaCredito;
use App\Models\MedioPago;
use App\Models\TipoCambio;
use App\Models\Area;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Clases\Funciones;
use DateTime;

class Integrador extends Component
{
    public $idCounter=1,$boleto="",$selectedGds="sabre";
    public $idRegistro,$numeroBoleto,$numeroFile,$fechaEmision,$idCliente,
    $idTipoFacturacion,$idTipoDocumento=6,$idArea=1,$idVendedor,$idConsolidador=2,$codigoReserva,
    $fechaReserva,$idGds=2,$idTipoTicket=1,$tipoRuta="NACIONAL",$tipoTarifa="NORMAL",$idAerolinea=7,
    $origen="BSP",$pasajero,
    $idTipoPasajero=1,$ruta,$destino,$idDocumento,$tipoCambio,$idMoneda=2,$tarifaNeta=0,$igv=0,
    $otrosImpuestos=0,$yr=0,$hw=0,$xm=0,$total=0,$totalOrigen=0,$porcentajeComision,$montoComision=0,
    $descuentoCorporativo,$codigoDescCorp,$tarifaNormal,$tarifaAlta,$tarifaBaja,
    $idTipoPagoConsolidador,$centroCosto,$cod1,$cod2,$cod3,$cod4,$observaciones,$estado=1,
    $usuarioCreacion,$fechaCreacion,$usuarioModificacion,$fechaModificacion,$checkFile;

    public $ciudadSalida,$ciudadLlegada,$idAerolineaRuta,$vuelo,$clase,$fechaSalida,$horaSalida,$fechaLlegada,
            $horaLlegada,$farebasis;

    public $idMedioPago,$idTarjetaCredito,$numeroTarjeta,$monto,$fechaVencimientoTC,$boletoPagos;

    public function render()
    { 
        $counters = Counter::all()->sortBy('nombre');
        $areas = Area::all()->sortBy('descripcion');
        return view('livewire.gestion.integrador',compact('counters','areas'));
    }

    public function obtenerBoleto(){
        $lineas = explode("\n",$this->boleto);
        $lineasBoleto = array_values($lineas);
        if ($this->selectedGds == 'sabre') {
            $this->parsearSabre($lineasBoleto);
        }else if($this->selectedGds == 'kiu'){
            $this->parsearKiu($lineasBoleto);
        }
        else if($this->selectedGds == 'ndc'){
            $this->parsearNdc($lineasBoleto);
        }
        else if($this->selectedGds == 'amadeus'){
            $this->parsearAmadeus($lineasBoleto);
        }
    }
    public function parsearSabre($boleto){
        $contador = 0;
        if (count($boleto) < 5) {
            session()->flash('error', 'Formato incorrecto del boleto.');
            return;
        }
        foreach ($boleto as $linea) {
            $contador = $contador + 1;
            //Obtener Pasajero
            $posPasajero = strpos($linea,"NAME:");
            if($posPasajero !== false){
                $this->pasajero = str_replace(" ","",$linea);
                $this->pasajero = str_replace("NAME:","",$this->pasajero);
                $this->pasajero = str_replace("/"," ",$this->pasajero);
            }

            //Obtener Aerolínea y Numero de Boleto
            $posBoleto = strpos($linea,"ETKT");
            if($posBoleto !== false){
                $codigoAerolinea = substr($linea,$posBoleto+10,3);
                $oAerolinea = Aerolinea::where('codigoIata',$codigoAerolinea)->first();
                $this->idAerolinea = $oAerolinea->id;

                $this->numeroBoleto = substr($linea,-10);
                $oBoleto = Boleto::where('numeroBoleto',$this->numeroBoleto)->first();
                if($oBoleto){
                    session()->flash('error', 'El boleto ya está integrado.');
                    return;
                }
            }
            
            //Obtener Código de Reserva
            $posPnr = strpos($linea,"BOOKING REFERENCE:");
            if ($posPnr !== false) {
                $this->codigoReserva = substr($linea,$posPnr+19,6);
            }

            //Obtener Fecha de Emision
            $posFechaEmision = strpos($linea,"DATE OF ISSUE:");
            if ($posFechaEmision !== false) {
                $fechaOriginal = substr($linea,$posFechaEmision+15,7);
                $fechaFormat = Carbon::createFromFormat('dMy',$fechaOriginal);
                $this->fechaEmision = $fechaFormat->format('Y-m-d');

                $tc = TipoCambio::where('fechaCambio',$this->fechaEmision)->first();
                if($tc){
                    $this->tipoCambio = $tc->montoCambio;
                }else{
                    $this->tipoCambio = 0.00;
                }
            }
            
            //Obtener Cliente
            $posCliente = strpos($linea,"NAME REF:");
            if ($posCliente !== false) {
                $posRuc = strpos($linea,"RUC");
                if ($posRuc !== false) {
                    $ruc = substr($linea,$posCliente+13,11);
                    $oCliente = Cliente::where('numeroDocumentoIdentidad',$ruc)->first();
                    if ($oCliente) {
                        $this->idCliente = $oCliente->id;
                        $this->idTipoFacturacion = $oCliente->tipoFacturacion;
                        $this->idArea = $oCliente->area;
                        $this->idVendedor = $oCliente->vendedor;
                    }
                }else{
                    $doc = substr($linea,-8);
                    $oCliente = Cliente::where('numeroDocumentoIdentidad',$doc)->first();
                    if ($oCliente) {
                        $this->idCliente = $oCliente->id;
                        $this->idTipoFacturacion = $oCliente->tipoFacturacion;
                        $this->idArea = $oCliente->area;
                        $this->idVendedor = $oCliente->vendedor;
                    }
                }
            }

            //Obtener Ruta / Destino
            $posRuta = strpos($linea,"FARE CALC:");
            if ($posRuta !== false) {
                $cadena = Str::remove(range(0,9),$linea);
                $cadena = Str::remove("USD",$cadena);
                $cadena = Str::remove("END",$cadena);
                // $cadena = Str::remove(".",$cadena);
                $cadena = str_replace("."," ",$cadena);
                
                $cadena = Str::remove("NUC",$cadena);
                $palabras = Str::of($cadena)->explode(' ');
                $palabras3 = $palabras->filter(function($palabra){
                    return Str::length($palabra) == 3;
                });
                foreach ($palabras3 as $word) {
                    $this->ruta = $this->ruta . $word . "/";
                }
                $this->ruta = substr($this->ruta,0,strlen($this->ruta)-1);
                
                $dest = str_replace("/","",$this->ruta);
                $incioCadena = round(((strlen($dest) / 3) / 2),0,PHP_ROUND_HALF_DOWN) * 3;
                $this->destino =  substr($dest, $incioCadena, 3);
            }

            //Obtener Forma de Pago
            $posFpago = strpos($linea,"FORM OF PAYMENT:");
            if ($posFpago !== false){
                if(strpos($linea,"CA")){
                    $oTc = TarjetaCredito::where('codigo','XX')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','009')->first();
                    $this->idMedioPago = $oMp->id;
                }
                if(strpos($linea,"VISA")){
                    $oTc = TarjetaCredito::where('codigo','VI')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','006')->first();
                    $this->idMedioPago = $oMp->id;
                }
                if(strpos($linea,"MASTERCARD")){
                    $oTc = TarjetaCredito::where('codigo','MA')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','006')->first();
                    $this->idMedioPago = $oMp->id;
                }
                if(strpos($linea,"MASTER CARD")){
                    $oTc = TarjetaCredito::where('codigo','MA')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','006')->first();
                    $this->idMedioPago = $oMp->id;
                }
                if(strpos($linea,"DINERS CLUB")){
                    $oTc = TarjetaCredito::where('codigo','DC')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','006')->first();
                    $this->idMedioPago = $oMp->id;
                }
                if(strpos($linea,"AMERICAN EXPRESS")){
                    $oTc = TarjetaCredito::where('codigo','AX')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','006')->first();
                    $this->idMedioPago = $oMp->id;
                }
            }

            // Obtener Tarifas
            $posDy = strpos($linea,"DY");
            if ($posDy !== false) {
                $this->tipoRuta = "INTERNACIONAL";
            }else{
                $this->tipoRuta = "NACIONAL";
            }
            $posTNeta = strpos($linea,"FARE:");
            if ($posTNeta !== false) {
                $neto = substr($linea,$posTNeta+9,7);
                $this->tarifaNeta = $neto;
            }
            $posTax = strpos($linea,"TAX:");
            if ($posTax !== false) {
                $tax = substr($linea,$posTax+8,7);
                $this->otrosImpuestos = $tax;
            }
            $posPe = strpos($linea,"PE   ");
            if ($posPe !== false) {
                $pe = substr($linea,$posPe-6,6);
                $this->igv = trim($pe);
            }
            $posYr = strpos($linea,"YR   ");
            if ($posYr !== false) {
                $nyr = substr($linea,$posYr-6,6);
                $this->yr = trim($nyr);
            }
            
            // dd($this->tipoRuta);
        }
        $this->tarifaNeta = $this->tarifaNeta + $this->yr;
        $this->otrosImpuestos = $this->otrosImpuestos - $this->igv - $this->yr;
        
        $this->grabarBoleto();
        // dd($this->idAerolinea);
    }

    public function grabarBoleto(){
        $area = Area::find($this->idArea);
        $boleto = new Boleto();
        $funciones = new Funciones();
        $boleto->numeroBoleto = $this->numeroBoleto;
        if($this->checkFile){
            $boleto->numeroFile = $this->numeroFile;
        }else{
            $file = $funciones->generaFile('FILES');
            $boleto->numeroFile = $area->codigo . str_pad($file,7,"0",STR_PAD_LEFT);
        }
        
        $boleto->idCliente = $this->idCliente;
        $boleto->idSolicitante = 0;
        $boleto->fechaEmision = $this->fechaEmision;
        $boleto->idCounter = $this->idCounter;
        $boleto->idTipoFacturacion = 1;
        $boleto->idTipoDocumento = 6;
        $boleto->idArea = $this->idArea;
        $boleto->idVendedor = 1;
        $boleto->idConsolidador = 1;
        $boleto->codigoReserva = $this->codigoReserva;
        $boleto->fechaReserva = $this->fechaEmision;
        $boleto->idGds = $this->idGds;
        $boleto->idTipoTicket = 1;
        $boleto->tipoRuta = $this->tipoRuta;
        $boleto->tipoTarifa = 'NORMAL';
        $boleto->idAerolinea = $this->idAerolinea;
        $boleto->origen = 'BSP';
        $boleto->pasajero = $this->pasajero;
        $boleto->idTipoPasajero = 1;
        $boleto->ruta = $this->ruta;
        $boleto->destino = $this->destino;
        $boleto->tipoCambio = $this->tipoCambio;
        $boleto->idMoneda = 1;
        $boleto->tarifaNeta = $this->tarifaNeta;
        $boleto->inafecto = 0;
        $boleto->igv = $this->igv;
        $boleto->otrosImpuestos = $this->otrosImpuestos;
        $boleto->xm = 0;
        $boleto->total = $this->tarifaNeta + $this->igv + $this->otrosImpuestos;
        $boleto->totalOrigen = $this->tarifaNeta + $this->igv + $this->otrosImpuestos;
        $boleto->porcentajeComision = 0;
        $boleto->montoComision = 0;
        $boleto->descuentoCorporativo = 0;
        $boleto->codigoDescCorp = ' ';
        $boleto->tarifaNormal = 0;
        $boleto->tarifaAlta = 0;
        $boleto->tarifaBaja = 0;
        $boleto->idTipoPagoConsolidador = 6;
        $boleto->centroCosto = ' ';
        $boleto->cod1 = ' ';
        $boleto->cod2 = ' ';
        $boleto->cod3 = ' ';
        $boleto->cod4 = ' ';
        $boleto->observaciones = ' ';
        $boleto->estado = 1;
        $boleto->usuarioCreacion = auth()->user()->id;
        $boleto->save();
        $this->grabarPagosSabre($boleto->id);
        try {
            // $boleto->save();
            // $this->grabarRutasSabre1($boleto->id);
            // $this->grabarRutasSabre2($boleto->id);
            // $this->grabarPagosSabre($boleto->id);
            return redirect()->route('listaBoletos');
        } catch (\Throwable $th) {
            session()->flash('error', 'Ocurrió un error intentando grabar.');
        }
    }
    public function parsearKiu($boleto){
        $contador = 0;
        if (count($boleto) < 5) {
            session()->flash('error', 'Formato incorrecto del boleto.');
            return;
        }
        foreach ($boleto as $linea) {
            $contador = $contador + 1;
            //Obtener Pasajero
            $posPasajero = strpos($linea,"NAME/NOMBRE:");
            if($posPasajero !== false){
                $this->pasajero = substr($linea,$posPasajero+13,25);
                $this->pasajero = trim($this->pasajero);
                $this->pasajero = str_replace("/"," ",$this->pasajero);
            }

            //Obtener Aerolínea y Numero de Boleto
            $posBoleto = strpos($linea,"TICKET NBR:");
            if($posBoleto !== false){
                $codigoAerolinea = substr($linea,$posBoleto+12,3);
                $oAerolinea = Aerolinea::where('codigoIata',$codigoAerolinea)->first();
                $this->idAerolinea = $oAerolinea->id;

                $this->numeroBoleto = substr(trim($linea),-10);
                $oBoleto = Boleto::where('numeroBoleto',$this->numeroBoleto)->first();
                if($oBoleto){
                    session()->flash('error', 'El boleto ya está integrado.');
                    return;
                }
            }
            
            //Obtener Código de Reserva
            $posPnr = strpos($linea,"BOOKING REF./CODIGO DE RESERVA:");
            if ($posPnr !== false) {
                $this->codigoReserva = substr(trim($linea),-6);
            }

            //Obtener Fecha de Emision
            $posFechaEmision = strpos($linea,"ISSUE DATE/FECHA DE EMISION:");
            if ($posFechaEmision !== false) {
                $fechaOriginal = substr($linea,$posFechaEmision+29,11);
                
                $fechaFormat = Carbon::createFromFormat('d M Y',$fechaOriginal);
                $this->fechaEmision = $fechaFormat->format('Y-m-d');
                
                $tc = TipoCambio::where('fechaCambio',$this->fechaEmision)->first();
                if($tc){
                    $this->tipoCambio = $tc->montoCambio;
                }else{
                    $this->tipoCambio = 0.00;
                }
            }
            
            //Obtener Cliente
            $posCliente = strpos($linea,"RUC           :");
            if ($posCliente !== false) {
                $posRuc = strpos($linea,"RUC");
                if ($posRuc !== false) {
                    $ruc = substr($linea,-11);
                    $oCliente = Cliente::where('numeroDocumentoIdentidad',$ruc)->first();
                    if ($oCliente) {
                        $this->idCliente = $oCliente->id;
                        $this->idTipoFacturacion = $oCliente->tipoFacturacion;
                        $this->idArea = $oCliente->area;
                        $this->idVendedor = $oCliente->vendedor;
                    }
                }else{
                    $doc = substr($linea,-8);
                    $oCliente = Clinte::where('numeroDocumentoIdentidad',$doc)->first();
                    if ($oCliente) {
                        $this->idCliente = $oCliente->id;
                        $this->idTipoFacturacion = $oCliente->tipoFacturacion;
                        $this->idArea = $oCliente->area;
                        $this->idVendedor = $oCliente->vendedor;
                    }
                }
            }

            //Obtener Ruta / Destino
            $posRuta = strpos($linea,"FARE CALC./CALCULO DE TARIFA:");
            if ($posRuta !== false) {
                $cadena = Str::remove(range(0,9),$linea);
                $cadena = Str::remove(".",$cadena);
                $cadena = Str::remove("NUC",$cadena);
                $palabras = Str::of($cadena)->explode(' ');
                $palabras3 = $palabras->filter(function($palabra){
                    return Str::length($palabra) == 3;
                });
                foreach ($palabras3 as $word) {
                    $this->ruta = $this->ruta . $word . "/";
                }
                $this->ruta = substr($this->ruta,0,strlen($this->ruta)-1);
                
                $dest = str_replace("/","",$this->ruta);
                $incioCadena = round(((strlen($dest) / 3) / 2),0,PHP_ROUND_HALF_DOWN) * 3;
                $this->destino =  substr($dest, $incioCadena, 3);
            }

            //Obtener Forma de Pago
            $posFpago = strpos($linea,"FORM OF PAYMENT/FORMA DE PAGO      :");
            if ($posFpago !== false){
                if(strpos($linea,"CASH")){
                    $oTc = TarjetaCredito::where('codigo','XX')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','009')->first();
                    $this->idMedioPago = $oMp->id;
                }
                if(strpos($linea,"VISA")){
                    $oTc = TarjetaCredito::where('codigo','VI')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','006')->first();
                    $this->idMedioPago = $oMp->id;
                }
                if(strpos($linea,"MASTERCARD")){
                    $oTc = TarjetaCredito::where('codigo','MA')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','006')->first();
                    $this->idMedioPago = $oMp->id;
                }
                if(strpos($linea,"MASTER CARD")){
                    $oTc = TarjetaCredito::where('codigo','MA')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','006')->first();
                    $this->idMedioPago = $oMp->id;
                }
                if(strpos($linea,"DINERS CLUB")){
                    $oTc = TarjetaCredito::where('codigo','DC')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','006')->first();
                    $this->idMedioPago = $oMp->id;
                }
                if(strpos($linea,"AMERICAN EXPRESS")){
                    $oTc = TarjetaCredito::where('codigo','AX')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','006')->first();
                    $this->idMedioPago = $oMp->id;
                }
            }

            // Obtener Tarifas
            
            $posTNeta = strpos($linea,"AIR FARE/TARIFA :");
            if ($posTNeta !== false) {
                $neto = substr($linea,-8);
                $this->tarifaNeta = trim($neto);
            }
            $posTax = strpos($linea,"TAX/IMPUESTOS   :");
            if ($posTax !== false) {
                $posPe = strpos($linea,"PE");
                if ($posPe !== false) {
                    $pe = substr($linea,$posPe-6,6);
                    $this->igv = trim($pe);
                }
                $posHw = strpos($linea,"HW");
                if ($posHw !== false) {
                    $hw = substr($linea,$posHw-6,6);
                    $this->hw = trim($hw);
                }
            }
            
            // dd($this->tipoRuta);
        }
        $this->otrosImpuestos = $this->hw;
        
        $this->grabarBoleto();
        // dd($this->idAerolinea);
        
    }

    public function parsearNdc($boleto){
        $contador = 0;
        $contPE = 0;
        if (count($boleto) < 5) {
            session()->flash('error', 'Formato incorrecto del boleto.');
            return;
        }
        foreach ($boleto as $linea) {
            $contador = $contador + 1;
            
            //Obtener Pasajero
            $posPasajero = strpos($linea,"PASSENGER NAME:");
            if($posPasajero !== false){
                $this->pasajero = trim(str_replace("PASSENGER NAME:","",$linea));
            }
            
            //Obtener Aerolínea y Numero de Boleto
            $posBoleto = strpos($linea,"TICKETING NUMBER:");
            if($posBoleto !== false){
                $codigoAerolinea = substr($linea,$posBoleto+18,3);
                $oAerolinea = Aerolinea::where('codigoIata',$codigoAerolinea)->first();
                $this->idAerolinea = $oAerolinea->id;
                $this->numeroBoleto = substr($linea,21,10);
                $oBoleto = Boleto::where('numeroBoleto',$this->numeroBoleto)->first();
                if($oBoleto){
                    session()->flash('error', 'El boleto ya está integrado.');
                    return;
                }
            }
            
            //Obtener Código de Reserva
            $posPnr = strpos($linea,"BOOKING REFERENCE:");
            if ($posPnr !== false) {
                $this->codigoReserva = substr($linea,$posPnr+19,6);
            }

            //Obtener Fecha de Emision
            $posFechaEmision = strpos($linea,"DATE OF ISSUE:");
            if ($posFechaEmision !== false) {
                $this->fechaEmision = substr($linea,$posFechaEmision+15,10);

                $tc = TipoCambio::where('fechaCambio',$this->fechaEmision)->first();
                if($tc){
                    $this->tipoCambio = $tc->montoCambio;
                }else{
                    $this->tipoCambio = 0.00;
                }
            }
            
            //Obtener Cliente
            $posCliente = strpos($linea,"DOCUMENT NUMBER:");
            if ($posCliente !== false) {
                $ruc = substr($linea,$posCliente+17,11);
                $oCliente = Cliente::where('numeroDocumentoIdentidad',$ruc)->first();
                if ($oCliente) {
                    $this->idCliente = $oCliente->id;
                    $this->idTipoFacturacion = $oCliente->tipoFacturacion;
                    $this->idArea = $oCliente->area;
                    $this->idVendedor = $oCliente->vendedor;
                }
            }

            // //Obtener Ruta / Destino
            // $posRuta = strpos($linea,"FARE CALC:");
            // if ($posRuta !== false) {
            //     $cadena = Str::remove(range(0,9),$linea);
            //     $cadena = Str::remove(".",$cadena);
            //     $cadena = Str::remove("NUC",$cadena);
            //     $palabras = Str::of($cadena)->explode(' ');
            //     $palabras3 = $palabras->filter(function($palabra){
            //         return Str::length($palabra) == 3;
            //     });
            //     foreach ($palabras3 as $word) {
            //         $this->ruta = $this->ruta . $word . "/";
            //     }
            //     $this->ruta = substr($this->ruta,0,strlen($this->ruta)-1);

            //     $dest = str_replace("/","",$this->ruta);
            //     $incioCadena = round(((strlen($dest) / 3) / 2),0,PHP_ROUND_HALF_DOWN) * 3;
            //     $this->destino =  substr($dest, $incioCadena, 3);
            // }

            //Obtener Forma de Pago
            $posFpago = strpos($linea,"PAYMENT METHOD:");
            if ($posFpago !== false){
                if(strpos($linea,"TC")){
                    $this->idTarjetaCredito = 2;
                    $this->idMedioPago = 6;
                }else{
                    $this->idTarjetaCredito = 1;
                    $this->idMedioPago = 8;
                }
            }

            // Obtener Tarifas
            // $posDy = strpos($linea,"DY");
            // if ($posDy !== false) {
            //     $this->tipoRuta = "INTERNACIONAL";
            // }else{
            //     $this->tipoRuta = "NACIONAL";
            // }
            $posTNeta = strpos($linea,"FARE: USD");
            if ($posTNeta !== false) {
                $neto = substr($linea,$posTNeta+9,7);
                $this->tarifaNeta = trim($neto);
            }else{
                $posTNeta = strpos($linea,"FARE: ");
                if ($posTNeta !== false) {
                    $neto = substr($linea,$posTNeta+6,7);
                    $this->tarifaNeta = trim($neto);
                }
            }
            
            $posPe = strpos($linea,"PE: USD");
            if ($posPe !== false) {
                $pe = substr($linea,$posPe+7,6);
                $this->igv = trim($pe);
            }else{
                $posPe = strpos($linea,"PE: ");
                if ($posPe !== false) {
                    $contPE = $contPE + 1;
                    if($contPE == 2){
                        $pe = substr($linea,$posPe+4,6);
                        $this->igv = trim($pe);
                    } 
                } 
            }

            $posHw = strpos($linea,"HW: USD");
            if ($posHw !== false) {
                $nhw = substr($linea,$posHw+7,6);
                $this->hw = trim($nhw);
            }else{
                $posHw = strpos($linea,"HW:");
                if ($posHw !== false){
                    $nhw = substr($linea,$posHw+4,6);
                    $this->hw = trim($nhw);
                }
                
            }
            
            // // dd($this->tipoRuta);
        }
        $this->otrosImpuestos = $this->hw;
        
        $this->grabarBoleto();
        // dd($this->idAerolinea);
    }

    public function parsearAmadeus($boleto){
        $contador = 0;
        $contPE = 0;
        if (count($boleto) < 5) {
            session()->flash('error', 'Formato incorrecto del boleto.');
            return;
        }
        foreach ($boleto as $linea) {
            $contador = $contador + 1;
            
            //Obtener Pasajero
            $posPasajero = strpos($linea,"Traveler ");
            if($posPasajero !== false){
                $this->pasajero = trim(str_replace("Traveler ","",$linea));
                $this->pasajero = trim(str_replace(" (ADT)","",$this->pasajero));
            }
            $posPasajero = strpos($linea,"Viajero ");
            if($posPasajero !== false){
                $this->pasajero = trim(str_replace("Viajero ","",$linea));
                $this->pasajero = trim(str_replace(" (ADT)","",$this->pasajero));
            }

            //Obtener Aerolínea y Numero de Boleto
            $posBoleto = strpos($linea,"Ticket: ");
            if($posBoleto !== false){
                $codigoAerolinea = substr($linea,$posBoleto+8,3);
                $oAerolinea = Aerolinea::where('codigoIata',$codigoAerolinea)->first();
                $this->idAerolinea = $oAerolinea->id;
                
                $this->numeroBoleto = substr($linea,12,10);
                $oBoleto = Boleto::where('numeroBoleto',$this->numeroBoleto)->first();
                if($oBoleto){
                    session()->flash('error', 'El boleto ya está integrado.');
                    return;
                }
            }
            $posBoleto = strpos($linea,"Billete Electrónico: ");
            if($posBoleto !== false){
                $codigoAerolinea = substr($linea,$posBoleto+22,3);
                $oAerolinea = Aerolinea::where('codigoIata',$codigoAerolinea)->first();
                $this->idAerolinea = $oAerolinea->id;
                
                $this->numeroBoleto = substr($linea,26,10);
                $oBoleto = Boleto::where('numeroBoleto',$this->numeroBoleto)->first();
                if($oBoleto){
                    session()->flash('error', 'El boleto ya está integrado.');
                    return;
                }
            }
            
            //Obtener Código de Reserva
            $posPnr = strpos($linea,"Booking ref:");
            if ($posPnr !== false) {
                $this->codigoReserva = substr($linea,$posPnr+13,6);
            }
            $posPnr = strpos($linea,"Loc. Reserva: ");
            if ($posPnr !== false) {
                $this->codigoReserva = substr($linea,$posPnr+14,6);
            }

            //Obtener Fecha de Emision
            $posFechaEmision = strpos($linea,"Issue date:");
            if ($posFechaEmision !== false) {
                $fecEmision = substr($linea,$posFechaEmision+12,30);
                $fecEmision = str_replace(" Baggage","",$fecEmision);
                $datetime = DateTime::createFromFormat('d F y', $fecEmision);
                $this->fechaEmision = $datetime->format('Y-m-d');
                
                $tc = TipoCambio::where('fechaCambio',$this->fechaEmision)->first();
                if($tc){
                    $this->tipoCambio = $tc->montoCambio;
                }else{
                    $this->tipoCambio = 0.00;
                }
            }
            $posFechaEmision = strpos($linea,"Fecha de Emisión: ");
            if ($posFechaEmision !== false) {
                $fecEmision = substr($linea,$posFechaEmision+18,30);
                $fecEmision = trim(str_replace(" Equipaje","",$fecEmision));
                $fecEmision = str_replace("ENERO","JANUARY",$fecEmision);
                $fecEmision = str_replace("FEBRERO","FEBRUARY",$fecEmision);
                $fecEmision = str_replace("MARZO","MARCH",$fecEmision);
                $fecEmision = str_replace("ABRIL","APRIL",$fecEmision);
                $fecEmision = str_replace("MAYO","MAY",$fecEmision);
                $fecEmision = str_replace("JUNIO","JUNE",$fecEmision);
                $fecEmision = str_replace("JULIO","JULY",$fecEmision);
                $fecEmision = str_replace("AGOSTO","AUGUST",$fecEmision);
                $fecEmision = str_replace("SEPTIEMBRE","SEPTEMBER",$fecEmision);
                $fecEmision = str_replace("OCTUBRE","OCTOBER",$fecEmision);
                $fecEmision = str_replace("NOVIEMBRE","NOVEMBER",$fecEmision);
                $fecEmision = str_replace("DICIEMBRE","DECEMBER",$fecEmision);
                $datetime = DateTime::createFromFormat('d M y', $fecEmision);
                $this->fechaEmision = $datetime->format('Y-m-d');
                $tc = TipoCambio::where('fechaCambio',$this->fechaEmision)->first();
                if($tc){
                    $this->tipoCambio = $tc->montoCambio;
                }else{
                    $this->tipoCambio = 0.00;
                }
            }
            
            //Obtener Cliente
            $posCliente = strpos($linea,"RUC2");
            if ($posCliente !== false) {
                $ruc = substr($linea,$posCliente+3,11);
                $oCliente = Cliente::where('numeroDocumentoIdentidad',$ruc)->first();
                if ($oCliente) {
                    $this->idCliente = $oCliente->id;
                    $this->idTipoFacturacion = $oCliente->tipoFacturacion;
                    $this->idArea = $oCliente->area;
                    $this->idVendedor = $oCliente->vendedor;
                }
            }
            if(!$this->idCliente){
                $posClienteNI = strpos($linea,"NI");
                if ($posClienteNI !== false){
                    if(strlen($linea) == 10){
                        $dni = substr($linea,2,8);
                        $oCliente = Cliente::where('numeroDocumentoIdentidad',$dni)->first();
                        if ($oCliente) {
                            $this->idCliente = $oCliente->id;
                            $this->idTipoFacturacion = $oCliente->tipoFacturacion;
                            $this->idArea = $oCliente->area;
                            $this->idVendedor = $oCliente->vendedor;
                        }
                    }
                }
            }

            //Obtener Ruta / Destino
            $posRuta = strpos($linea,"Fare Calculation :");
            if ($posRuta !== false) {
                $cadena = Str::remove(range(0,9),$linea);
                $cadena = str_replace("."," ",$cadena);
                $cadena = Str::remove("NUC",$cadena);
                $cadena = Str::remove("USD",$cadena);
                $cadena = Str::remove("END",$cadena);
                $cadena = Str::remove("ROE",$cadena);
                $cadena = Str::remove("X/",$cadena);
                $palabras = Str::of($cadena)->explode(' ');
                $palabras3 = $palabras->filter(function($palabra){
                    return Str::length($palabra) == 3;
                });
                foreach ($palabras3 as $word) {
                    $this->ruta = $this->ruta . $word . "/";
                }
                $this->ruta = substr($this->ruta,0,strlen($this->ruta)-1);
                $dest = str_replace("/","",$this->ruta);
                $incioCadena = round(((strlen($dest) / 3) / 2),0,PHP_ROUND_HALF_DOWN) * 3;
                $this->destino =  substr($dest, $incioCadena, 3);
            }
            $posRuta = strpos($linea,"Cálculo de Tarifa :");
            if ($posRuta !== false) {
                $cadena = Str::remove(range(0,9),$linea);
                $cadena = str_replace("."," ",$cadena);
                $cadena = Str::remove("NUC",$cadena);
                $cadena = Str::remove("USD",$cadena);
                $cadena = Str::remove("END",$cadena);
                $cadena = Str::remove("ROE",$cadena);
                $cadena = Str::remove("X/",$cadena);
                $palabras = Str::of($cadena)->explode(' ');
                $palabras3 = $palabras->filter(function($palabra){
                    return Str::length($palabra) == 3;
                });
                foreach ($palabras3 as $word) {
                    $this->ruta = $this->ruta . $word . "/";
                }
                $this->ruta = substr($this->ruta,0,strlen($this->ruta)-1);
                $dest = str_replace("/","",$this->ruta);
                $incioCadena = round(((strlen($dest) / 3) / 2),0,PHP_ROUND_HALF_DOWN) * 3;
                $this->destino =  substr($dest, $incioCadena, 3);
            }

            //Obtener Forma de Pago
            $posFpago = strpos($linea,"Form of payment :");
            if ($posFpago !== false){
                if(strpos($linea,"CCVI")){
                    $this->idTarjetaCredito = 2;
                    $this->idMedioPago = 6;
                }else{
                    $this->idTarjetaCredito = 1;
                    $this->idMedioPago = 8;
                }
            }
            $posFpago = strpos($linea,"Modo de pago :");
            if ($posFpago !== false){
                if(strpos($linea,"CCVI")){
                    $this->idTarjetaCredito = 2;
                    $this->idMedioPago = 6;
                }else{
                    $this->idTarjetaCredito = 1;
                    $this->idMedioPago = 8;
                }
            }

            // Obtener Tarifas
            // $posDy = strpos($linea,"DY");
            // if ($posDy !== false) {
            //     $this->tipoRuta = "INTERNACIONAL";
            // }else{
            //     $this->tipoRuta = "NACIONAL";
            // }
            
            $posTNeta = strpos($linea,"Air Fare :");
            if ($posTNeta !== false) {
                $neto = substr($linea,$posTNeta+15,8);
                $this->tarifaNeta = trim($neto);
                $this->igv = $this->tarifaNeta * 0.18;
            }
            $posTNeta = strpos($linea,"Tarifa aérea :");
            if ($posTNeta !== false) {
                $neto = substr($linea,$posTNeta+19,8);
                $this->tarifaNeta = trim($neto);
                // $this->igv = $this->tarifaNeta * 0.18;
            }
            $posYR = strpos($linea,"Airline Surcharges :");
            if ($posYR !== false){
                $this->yr = substr($linea,$posYR+25,10);
                $this->yr = Str::remove("YR",$this->yr);
                $this->tarifaNeta = $this->tarifaNeta + $this->yr;
                $this->igv = $this->tarifaNeta * 0.18;
            }
            $posYR = strpos($linea,"Recargo De Aerolinea :");
            if ($posYR !== false){
                $this->yr = substr($linea,$posYR+27,10);
                $this->yr = Str::remove("YR",$this->yr);
                $this->tarifaNeta = $this->tarifaNeta + $this->yr;
                $this->igv = $this->tarifaNeta * 0.18;
            }
            
            // $posPe = strpos($linea,"PE: USD");
            // if ($posPe !== false) {
            //     $pe = substr($linea,$posPe+7,6);
            //     $this->igv = trim($pe);
            // }else{
            //     $posPe = strpos($linea,"PE: ");
            //     if ($posPe !== false) {
            //         $contPE = $contPE + 1;
            //         if($contPE == 2){
            //             $pe = substr($linea,$posPe+4,6);
            //             $this->igv = trim($pe);
            //         } 
            //     } 
            // }

            $posTotal = strpos($linea,"Total Amount");
            if ($posTotal !== false){
                $total = substr($linea,$posTotal+19,8);
                $this->otrosImpuestos = $total - $this->tarifaNeta - $this->igv;
            }
            $posTotal = strpos($linea,"Importe Total :");
            if ($posTotal !== false){
                $total = substr($linea,$posTotal+20,8);
                $this->otrosImpuestos = $total - $this->tarifaNeta - $this->igv;
            }
            
        }

        $this->idGds = 4;
        $this->grabarBoleto();
        // dd($this->idAerolinea);
    }

    public function grabarRutasNdc1($idBoleto){
        $boletoRuta = new BoletoRuta();
        $boletoRuta->idBoleto = $idBoleto;
        $boletoRuta->idAerolinea = 1;
        $boletoRuta->ciudadSalida = 'LIM';
        $boletoRuta->ciudadLlegada = 'PCL';
        $boletoRuta->vuelo = ' ';
        $boletoRuta->clase = 'S';
        $boletoRuta->fechaSalida = '2023-10-06';
        $boletoRuta->horaSalida = '1425';
        $boletoRuta->fechaLlegada = '2023-10-06';
        $boletoRuta->horaLlegada = '1545';
        $boletoRuta->farebasis = 'S00QP5ZB';
        $boletoRuta->idEstado = 1;
        $boletoRuta->usuarioCreacion = auth()->user()->id;
        $boletoRuta->save();
    }
    public function grabarRutasNdc2($idBoleto){
        $boletoRuta = new BoletoRuta();
        $boletoRuta->idBoleto = $idBoleto;
        $boletoRuta->idAerolinea = 1;
        $boletoRuta->ciudadSalida = 'PCL';
        $boletoRuta->ciudadLlegada = 'LIM';
        $boletoRuta->vuelo = ' ';
        $boletoRuta->clase = 'X';
        $boletoRuta->fechaSalida = '2023-10-15';
        $boletoRuta->horaSalida = '1310';
        $boletoRuta->fechaLlegada = '2023-10-15';
        $boletoRuta->horaLlegada = '1425';
        $boletoRuta->farebasis = 'X00QP5ZB';
        $boletoRuta->idEstado = 1;
        $boletoRuta->usuarioCreacion = auth()->user()->id;
        $boletoRuta->save();
    }
    public function grabarPagosNdc($idBoleto){
        $boletoPago = new BoletoPago();
        $boletoPago->idBoleto = $idBoleto;
        $boletoPago->idMedioPago = 6;
        $boletoPago->idTarjetaCredito = 2;
        $boletoPago->numeroTarjeta = ' ';
        $boletoPago->monto = 133.35;
        $boletoPago->fechaVencimientoTC = ' ';
        $boletoPago->idEstado = 1;
        $boletoPago->usuarioCreacion = auth()->user()->id;
        $boletoPago->save();
    }

    public function grabarRutasKiu1($idBoleto){
        $boletoRuta = new BoletoRuta();
        $boletoRuta->idBoleto = $idBoleto;
        $boletoRuta->idAerolinea = 3;
        $boletoRuta->ciudadSalida = 'IQT';
        $boletoRuta->ciudadLlegada = 'PCL';
        $boletoRuta->vuelo = '2I3132';
        $boletoRuta->clase = 'W';
        $boletoRuta->fechaSalida = '2023-09-25';
        $boletoRuta->horaSalida = '1500';
        $boletoRuta->fechaLlegada = '2023-09-25';
        $boletoRuta->horaLlegada = '1600';
        $boletoRuta->farebasis = 'WOW';
        $boletoRuta->idEstado = 1;
        $boletoRuta->usuarioCreacion = auth()->user()->id;
        $boletoRuta->save();
    }
    public function grabarRutasKiu2($idBoleto){
        $boletoRuta = new BoletoRuta();
        $boletoRuta->idBoleto = $idBoleto;
        $boletoRuta->idAerolinea = 3;
        $boletoRuta->ciudadSalida = 'PCL';
        $boletoRuta->ciudadLlegada = 'IQT';
        $boletoRuta->vuelo = '2I3131';
        $boletoRuta->clase = 'W';
        $boletoRuta->fechaSalida = '2023-09-30';
        $boletoRuta->horaSalida = '1325';
        $boletoRuta->fechaLlegada = '2023-09-30';
        $boletoRuta->horaLlegada = '1425';
        $boletoRuta->farebasis = 'WOW';
        $boletoRuta->idEstado = 1;
        $boletoRuta->usuarioCreacion = auth()->user()->id;
        $boletoRuta->save();
    }
    public function grabarPagosKiu($idBoleto){
        $boletoPago = new BoletoPago();
        $boletoPago->idBoleto = $idBoleto;
        $boletoPago->idMedioPago = 9;
        $boletoPago->idTarjetaCredito = 1;
        $boletoPago->numeroTarjeta = ' ';
        $boletoPago->monto = 223.56;
        $boletoPago->fechaVencimientoTC = ' ';
        $boletoPago->idEstado = 1;
        $boletoPago->usuarioCreacion = auth()->user()->id;
        $boletoPago->save();
    }

    public function grabarRutasSabre1($idBoleto){
        $boletoRuta = new BoletoRuta();
        $boletoRuta->idBoleto = $idBoleto;
        $boletoRuta->idAerolinea = 2;
        $boletoRuta->ciudadSalida = 'BOG';
        $boletoRuta->ciudadLlegada = 'LIM';
        $boletoRuta->vuelo = '75';
        $boletoRuta->clase = 'E';
        $boletoRuta->fechaSalida = '2023-09-20';
        $boletoRuta->horaSalida = '2130';
        $boletoRuta->fechaLlegada = '2023-09-20';
        $boletoRuta->horaLlegada = '0040';
        $boletoRuta->farebasis = 'EEOB2BRG';
        $boletoRuta->idEstado = 1;
        $boletoRuta->usuarioCreacion = auth()->user()->id;
        $boletoRuta->save();
    }
    public function grabarRutasSabre2($idBoleto){
        $boletoRuta = new BoletoRuta();
        $boletoRuta->idBoleto = $idBoleto;
        $boletoRuta->idAerolinea = 2;
        $boletoRuta->ciudadSalida = 'BOG';
        $boletoRuta->ciudadLlegada = 'LIM';
        $boletoRuta->vuelo = '52';
        $boletoRuta->clase = 'L';
        $boletoRuta->fechaSalida = '2023-09-22';
        $boletoRuta->horaSalida = '1840';
        $boletoRuta->fechaLlegada = '2023-09-22';
        $boletoRuta->horaLlegada = '2150';
        $boletoRuta->farebasis = 'LEOB2BRG';
        $boletoRuta->idEstado = 1;
        $boletoRuta->usuarioCreacion = auth()->user()->id;
        $boletoRuta->save();
    }
    public function grabarPagosSabre($idBoleto){
        $boletoPago = new BoletoPago();
        $boletoPago->idBoleto = $idBoleto;
        $boletoPago->idMedioPago = $this->idMedioPago;
        $boletoPago->idTarjetaCredito = $this->idTarjetaCredito;
        $boletoPago->numeroTarjeta = ' ';
        $boletoPago->monto = $this->tarifaNeta + $this->igv + $this->otrosImpuestos;
        $boletoPago->fechaVencimientoTC = ' ';
        $boletoPago->idEstado = 1;
        $boletoPago->usuarioCreacion = auth()->user()->id;
        $boletoPago->save();
    }
}
