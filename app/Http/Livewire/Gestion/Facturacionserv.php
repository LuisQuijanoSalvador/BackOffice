<?php

namespace App\Http\Livewire\Gestion;

use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use App\Models\moneda;
use App\Models\Boleto;
use App\Clases\Funciones;
use App\Models\Documento;
use App\Models\documentoDetalle;
use App\Models\Cliente;
use App\Models\TipoCambio;
use App\Clases\modelonumero;
use App\Models\Servicio;
use App\Models\Solicitante;
use App\Models\TipoDocumentoIdentidad;
use App\Models\Cargo;
use App\Models\MedioPago;
use App\Models\ServicioPago;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FacservacExport;

class Facturacionserv extends Component
{
    use WithPagination;

    public $search = "";
    public $sort= 'numeroFile';
    public $direction = 'asc';

    public $idRegistro,$idMoneda=1,$tipoCambio,$fechaEmision,$detraccion=0,$glosa="",$descripcion="",$idCliente,
            $tipoDocumentoIdentidad,$codigoDocumentoIdentidad,$descDocumentoIdentidad,$monedaLetra,$respSenda,
            $numeroTelefono,$chkMedioPago,$idMedioPagoCambio,$idMedioPago,$metodo_pago, $codigo_metodopago, 
            $desc_metodopago, $centroCosto, $totalNeto = 0,$totalInafecto = 0,$totalIGV = 0,$totalOtrosImpuestos = 0,
            $totalTotal = 0, $startDate, $endDate, $cod01;
    protected $servicios=[];

    public $selectedRows = [];

    public function mount(){
        $this->servicios = Servicio::where('numeroFile', 'like', "%$this->search%")
                            ->whereNull('idDocumento')
                            ->where('idTipoFacturacion',1)
                            ->where('estado',1)
                            ->orderBy($this->sort, $this->direction)
                            ->get();

        $fechaActual = Carbon::now();
        
        $this->fechaEmision = Carbon::parse($fechaActual)->format("Y-m-d");

        $tipoCambio = TipoCambio::where('fechaCambio',$this->fechaEmision)->first();
        if($tipoCambio){
            $this->tipoCambio = $tipoCambio->montoCambio;
        }else{
            $this->tipoCambio = 0.00;
        }
    }

    public function render()
    {
        $monedas = moneda::all()->sortBy('codigo');
        $clientes = Cliente::all()->sortBy('razonSocial');
        $medioPagos = MedioPago::all()->sortBy('codigo');
        
        return view('livewire.gestion.facturacionserv',compact('monedas','medioPagos','clientes'));
    }

    public function updatedfechaEmision($fechaEmision){
        $tipoCambio = TipoCambio::where('fechaCambio',$fechaEmision)->first();
        if($tipoCambio){
            $this->tipoCambio = $tipoCambio->montoCambio;
        }else{
            $this->tipoCambio = 0.00;
        }
        //dd($this->tipoCambio);
    }

    public function filtrar(){
        if ($this->idCliente and $this->startDate and $this->endDate) {

            $this->servicios = Servicio::where('idCliente', $this->idCliente)
                                ->whereNull('idDocumento')
                                ->where('idTipoFacturacion',1)
                                ->where('estado',1)
                                ->whereBetween('fechaEmision', [$this->startDate, $this->endDate])
                                ->orderBy($this->sort, $this->direction)
                                ->get();
                                // ->paginate(10);
        }else if (!$this->idCliente and $this->startDate and $this->endDate){
            $this->servicios = Servicio::where('idTipoFacturacion',1)
                                ->whereNull('idDocumento')
                                ->where('estado',1)
                                ->whereBetween('fechaEmision', [$this->startDate, $this->endDate])
                                ->orderBy($this->sort, $this->direction)
                                ->get();
                                // ->paginate(10);
        }else{
            session()->flash('error', 'Verifique los datos del filtro');
        }
        
    }

    public function emitirComprobante()
    {
        // 
        // $this->selectedRows contendrá los IDs de las filas seleccionadas
        $idsSeleccionados = $this->selectedRows;
        if (empty($idsSeleccionados)) {
            session()->flash('error', 'Debe seleccionar un servicio.');
            return false;
        } else {
            $servicios = Servicio::select('id','numeroServicio','numeroFile','idCliente','idSolicitante','fechaEmision','idCounter','idTipoFacturacion','idTipoDocumento','idArea','idVendedor','codigoReserva','fechaReserva','idGds','idTipoServicio','tipoRuta','tipoTarifa','idAerolinea','origen','pasajero','idTipoPasajero','ruta','destino','idDocumento','tipoCambio','idMoneda','tarifaNeta','inafecto','igv','otrosImpuestos','xm','total','totalOrigen','porcentajeComision','montoComision','descuentoCorporativo','codigoDescCorp','tarifaNormal','tarifaAlta','tarifaBaja','idTipoPagoConsolidador','centroCosto','cod1','cod2','cod3','cod4','observaciones','estado','usuarioCreacion','usuarioModificacion')
            ->whereIn('id',$this->selectedRows)
            ->get();

            foreach ($servicios as $servicio) {
            $this->totalNeto += $servicio->tarifaNeta;
            $this->totalInafecto += $servicio->inafecto;
            $this->totalIGV += $servicio->igv;
            $this->totalOtrosImpuestos += $servicio->otrosImpuestos;
            $this->totalTotal += $servicio->total;
            }

            $servicio = Servicio::find($this->selectedRows[0]);

            // $servicio = Servicio::find($idsSeleccionados);

            $this->crearDocumento($servicio, $servicios);
        }  
    }

    public function crearDocumento($dataServicioIn, $servicios){
        $dataServicio = $dataServicioIn;
        $documento = new Documento();
        $funciones = new Funciones();
        $numLetras = new modelonumero();

        switch ($dataServicio->idTipoDocumento) {
            case 6:
                $numComprobante = $funciones->numeroComprobante('DOCUMENTO DE COBRANZA');
                $numSerie = '0001';
                break;
            case 1:
                $numComprobante = $funciones->numeroComprobante('FACTURA');
                $numSerie = 'F001';
                break;
            case 2:
                $numComprobante = $funciones->numeroComprobante('BOLETA');
                $numSerie = 'B001';
                break;
        }
        $cliente = Cliente::find($dataServicio->idCliente);
        $this->numeroTelefono = $cliente->numeroTelefono;
        $fechaVencimiento = Carbon::parse($this->fechaEmision)->addDays($cliente->diasCredito);
        if ($dataServicio->tMoneda->codigo == 'USD') {
            $this->monedaLetra = 'DOLARES AMERICANOS';
        } elseif($dataServicio->tMoneda->codigo == 'PEN'){
            $this->monedaLetra = 'SOLES';
        }

        if($dataServicio->centroCosto){
            $this->centroCosto = $dataServicio->centroCosto;
        }else{
            $this->centroCosto = '';
        }
        if($dataServicio->cod01){
            $this->cod01 = $dataServicio->cod01;
        }else{
            $this->cod01 = '';
        }

        $servicioPago = ServicioPago::where('idServicio',$dataServicio->id)->first();
        if($servicioPago){
            $this->idMedioPago = $servicioPago->idMedioPago;
        }else{
            $this->idMedioPago = 6;
        }

        if($cliente->montoCredito > 0){
            $this->idMedioPago = 10;
        }
        if($this->chkMedioPago){
            $this->idMedioPago = $this->idMedioPagoCambio;
            if(!$this->idMedioPago){
                session()->flash('error', 'No ha seleccionado Medio de Pago');
                return;
            }
        }
        
        $solicitante = Solicitante::find($dataServicio->idSolicitante);
        $cSolic = '';
        if($solicitante){
            $cSolic = $solicitante->nombres;
        }
        if(strlen($this->glosa) < 5){
            $ruta = "";
            if($dataServicio->tBoleto){
                $ruta = $dataServicio->tBoleto->ruta;
            }
            $this->glosa = "";
            // $this->descripcion = 'SOLICITADO POR: ' . $cSolic . '<br>' . 'POR LA COMPRA DE BOLETO(S) AEREOS A FAVOR DE: <br>';
            foreach($servicios as $servicio){
                $this->descripcion = $this->descripcion . 'FEE POR BOLETO, PAX: ' . $servicio->pasajero . ' TKT: ' . $servicio->tBoleto->tAerolinea->codigoIata . ' - ' . $servicio->tBoleto->numeroBoleto . ' RUTA: ' . $servicio->tBoleto->ruta . ' ' . $servicio->tBoleto->tAerolinea->razonSocial .'<br>';
            }
            // $this->descripcion = $dataServicio->tTipoServicio->descripcion . 'SOLICITADO POR: ' . $cSolic .  "POR LA EMISION DE UN BOLETO AEREO A FAVOR DE PAX: " . $dataServicio->pasajero .  'EN LA RUTA: ' . $ruta . ' | ' . 'TKT: ' . $dataServicio->tBoleto->tAerolinea->codigoIata . ' - ' . $dataServicio->tBoleto->numeroBoleto . ' EN ' . $dataServicio->tBoleto->tAerolinea->razonSocial;
            // dd($this->descripcion);
        }else{
            $this->descripcion = $dataServicio->tTipoServicio->descripcion;
        }
        
        $this->tipoDocumentoIdentidad = $dataServicio->tCliente->tipoDocumentoIdentidad;
        $tipoDocId = TipoDocumentoIdentidad::find($this->tipoDocumentoIdentidad);
        $this->codigoDocumentoIdentidad = $tipoDocId->codigo;
        $this->descDocumentoIdentidad = $tipoDocId->descripcion;

        $totalLetras = $numLetras->numtoletras($dataServicio->total,$this->monedaLetra);
        
        $documento->idCliente = $dataServicio->idCliente;
        $documento->razonSocial = $dataServicio->tCliente->razonSocial;
        $documento->direccionFiscal = $dataServicio->tCliente->direccionFiscal;
        $documento->numeroDocumentoIdentidad = $dataServicio->tCliente->numeroDocumentoIdentidad;
        $documento->idTipoDocumento = $dataServicio->idTipoDocumento;
        $documento->tipoDocumento = $dataServicio->tTipoDocumento->codigo;
        $documento->serie = $numSerie;
        $documento->numero = $numComprobante;
        $documento->idMoneda = $dataServicio->idMoneda;
        $documento->moneda = $dataServicio->tMoneda->codigo;
        $documento->fechaEmision = $this->fechaEmision;
        $documento->fechaVencimiento = Carbon::parse($fechaVencimiento)->format("Y-m-d");
        $documento->detraccion = $this->detraccion;
        $documento->afecto = $this->totalNeto;
        $documento->inafecto = $this->totalInafecto;
        $documento->exonerado = 0;
        $documento->igv = $this->totalIGV;
        $documento->otrosImpuestos = $this->totalOtrosImpuestos;
        $documento->total = $this->totalTotal;
        $documento->totalLetras = $totalLetras;
        $documento->idMedioPago = $this->idMedioPago;
        $documento->glosa = $this->glosa;
        $documento->numeroFile = $dataServicio->numeroFile;
        $documento->tipoServicio = 1;
        $documento->documentoReferencia = "";
        $documento->idMotivoNC = 0;
        $documento->idMotivoND = 0;
        $documento->tipoCambio = $this->tipoCambio;
        $documento->idEstado = 1;
        $documento->usuarioCreacion = auth()->user()->id;
        $documento->usuarioModificacion = auth()->user()->id;
        $dataJson = "";
        
        $medioPago = MedioPago::find($this->idMedioPago);
        if($medioPago->id == 10){
            $this->metodo_pago = $medioPago->descripcion;
            $this->codigo_metodopago = "CRE";
            $this->desc_metodopago = $documento->total . "," . "1;" . $documento->total . ";" . $documento->fechaVencimiento;
        }else{
            $this->metodo_pago = $medioPago->descripcion;
            $this->codigo_metodopago = "CON";
            $this->desc_metodopago = "";
        }
        
        if($documento->idTipoDocumento == 6){
            if($documento->inafecto > 0){
                $dataJson = $this->enviaDCMixto($documento);
            }else{
                $dataJson = $this->enviaDC($documento);
            }
        }else{
            if($documento->inafecto > 0 && $documento->afecto > 0 && $documento->otrosImpuestos == 0){
                $dataJson = $this->enviaCPEMixto($documento);
                
            }elseif($documento->inafecto > 0 && $documento->afecto == 0){
                $dataJson = $this->enviaCPEInafecto($documento);

            }elseif($documento->inafecto > 0 && $documento->afecto > 0 && $documento->otrosImpuestos > 0){
                if($medioPago->id == 10){
                    $this->metodo_pago = $medioPago->descripcion;
                    $this->codigo_metodopago = "CRE";
                    $this->desc_metodopago = round($documento->total -$documento->otrosImpuestos,2) . "," . "1;" . round($documento->total -$documento->otrosImpuestos,2) . ";" . $documento->fechaVencimiento;
                }
                $dataJson = $this->enviaCPEMixto3($documento);

            }else{
                $dataJson = $this->enviaCPE($documento);
            }
        }
        $jsonDoc = json_encode($dataJson, JSON_PRETTY_PRINT);
        $documento->save();

        switch ($dataServicio->idTipoDocumento) {
            case 6:
                $funciones->grabarCorrelativo('DOCUMENTO DE COBRANZA',$numComprobante);
                break;
            case 1:
                $funciones->grabarCorrelativo('FACTURA',$numComprobante);
                break;
            case 2:
                $funciones->grabarCorrelativo('BOLETA',$numComprobante);
                break;
        }

        if ($this->respSenda['type'] == 'success') {
            $doc = Documento::find($documento->id);
            $doc->respuestaSunat = $this->respSenda['type'];
            $doc->jsonDoc = $jsonDoc;
            $doc->save();
            

            session()->flash('success', 'El documento se ha emitido correctamente');

        } else {
            session()->flash('error', 'Ocurrió un error enviando a Sunat');
            $doc = Documento::find($documento->id);
            $doc->jsonDoc = $jsonDoc;
            $doc->save();
        }
        
        
        // $idsSeleccionados = $this->selectedRows;
        // $servicio = Servicio::find($idsSeleccionados);
        // $servicio->idDocumento = $documento->id;
        // $servicio->save();

        Servicio::whereIn('id',$this->selectedRows)
        ->update(['idDocumento' => $documento->id]);

        if($documento->idMedioPago <> 6){
            $this->generarCargo($documento->id);
        }
        
        $this->glosa="";
        $this->limpiarVariables();
        $dataServicio = NULL;
    }

    public function limpiarVariables(){
        $this->idRegistro = 0;
        $this->idMoneda = 1;
        $this->tipoCambio = 0;
        $this->fechaEmision = NULL;
        $this->detraccion = 0;
        $this->glosa = "";
        $this->descripcion = "";
        $this->tipoDocumentoIdentidad = NULL;
        $this->codigoDocumentoIdentidad = NULL;
        $this->descDocumentoIdentidad = NULL;
        $this->monedaLetra = NULL;
        $this->respSenda = NULL;
        $this->numeroTelefono = NULL;
        $this->chkMedioPago = 0;
        $this->idMedioPagoCambio = NULL;
        $this->idMedioPago = NULL;
        $this->metodo_pago = NULL;
        $this->codigo_metodopago = NULL;
        $this->desc_metodopago = NULL;
        $this->centroCosto = NULL;
        $this->totalNeto = 0;
        $this->totalInafecto = 0;
        $this->totalIGV = 0;
        $this->totalOtrosImpuestos = 0;
        $this->totalTotal = 0;
        $this->selectedRows = NULL;
    }

    public function generarCargo($docId){
        $documento = Documento::find($docId);
        if($documento->idMedioPago <> 6){
            $cliente = Cliente::find($documento->idCliente);
            $servicios = Servicio::where('idDocumento',$documento->id)->get();
            $numServicios = $servicios->count();
            $servicio = Servicio::where('idDocumento',$documento->id)->first();
            $cargo = new Cargo();
            $cargo->idDocumento = $documento->id;
            $cargo->idCliente = $documento->idCliente;
            $cargo->idCobrador = $cliente->cobrador;
            $cargo->idCounter = $cliente->counter;
            $cargo->idProveedor = $servicio->idProveedor;
            if($servicio->idSolicitante){
                $cargo->idSolicitante = $servicio->idSolicitante;
            }
            $cargo->idServicio = $servicio->id;
            $cargo->montoCredito = $cliente->montoCredito;
            $cargo->diasCredito = $cliente->diasCredito;
            $cargo->fechaEmision = $documento->fechaEmision;
            $cargo->fechaVencimiento = $documento->fechaVencimiento;
            if($numServicios > 1){
                $cargo->numeroBoleto = 'SERVICIOS VARIOS';
                $cargo->pasajero = 'PASAJEROS VARIOS';
            }else{
                $cargo->numeroBoleto = $servicio->tTipoServicio->descripcion;
                $cargo->pasajero = $servicio->pasajero;
            }
            $cargo->tipoRuta = $servicio->tipoRuta;
            $cargo->ruta = $servicio->ruta;
            $cargo->moneda = $documento->moneda;
            $cargo->tarifaNeta = $documento->afecto;
            $cargo->inafecto = $documento->inafecto;
            $cargo->igv = $documento->igv;
            $cargo->otrosImpuestos = $documento->otrosImpuestos;
            $cargo->total = $documento->total;
            $cargo->tipoDocumento = $documento->tipoDocumento;
            $cargo->serieDocumento = $documento->serie;
            $cargo->numeroDocumento = str_pad($documento->numero,8,"0",STR_PAD_LEFT);
            $cargo->montoCargo = $documento->total;
            $cargo->tipoCambio = $documento->tipoCambio;
            $cargo->saldo = $documento->total;
            $cargo->idEstado = 1;
            $cargo->usuarioCreacion = auth()->user()->id;

            $cargo->save();
        }
    }

    public function enviaCPE($comprobante){
        
        $mensaje_detra = "";
        if($this->detraccion == 1){
            $mensaje_detra = "OPERACION SUJETA AL SISTEMA DE PAGO DE OBLIGACIONES TRIBUTARIAS CON EL GOBIERNO CENTRAL BANCO DE LA NACION: 00058327778";
        }

        // Datos a enviar en formato JSON
        $dataToSend = [
            "cabecera" => [
                "ruc_emisor" => "20604309027" ,
                "razonsocial_emisor"=> "AS TRAVEL PERU S.A.C",
                "direccion_emisor"=> "CAL.CALLE MARQUES DE MONTESCLAROS NRO. 165 DPTO. 104 URB. LA VIRREYNA LIMA - LIMA - SANTIAGO DE SURCO",
                "telefono_emisor"=> "(01) 972197067",
                "email_emisor"=> "facturaselectronicas@astravel.com.pe",
                "cod_domifiscal"=> "0000",
                "tiop_codi"=> "0101",
                "fecha"=> $comprobante->fechaEmision,
                "fvenc"=> $comprobante->fechaVencimiento,
                "tipodocu"=> $comprobante->tipoDocumento,
                "nro_serie_efact"=> $comprobante->serie,
                "tipo_moneda"=> $comprobante->moneda,
                "numero"=> str_pad($comprobante->numero,8,"0",STR_PAD_LEFT),
                "tipodocurefe"=> "",
                "numerorefe"=> "",
                "motivo_07_08"=> "",
                "descripcion_07_08"=> "",
                "fecharefe"=> "1900-01-01T00=>00=>00",
                "tipodoi"=> $this->codigoDocumentoIdentidad,
                "numerodoi"=> $comprobante->numeroDocumentoIdentidad,
                "desc_tipodocu"=> $this->descDocumentoIdentidad,
                "razonsocial"=> $comprobante->razonSocial,
                "direccion"=> $comprobante->direccionFiscal,
                "cliente"=> $comprobante->razonSocial,
                "email_cliente"=> "facturaselectronicas@astravel.com.pe",
                "email_cc"=> "",
                "codigo_cliente"=> $comprobante->idCliente,
                "rec_tele"=> $this->numeroTelefono,
                "rec_ubigeo"=> "",
                "rec_pais"=> "",
                "rec_depa"=> "",
                "rec_provi"=> "",
                "rec_distri"=> "",
                "rec_urb"=> "",
                "vendedor"=> "AS TRAVEL",
                "metodo_pago"=> $this->metodo_pago,
                "codigo_metodopago"=> $this->codigo_metodopago,
                "desc_metodopago"=> $this->desc_metodopago,
                "totalpagado_efectivo"=> "0.00",
                "vuelto"=> "0.00",
                "file_nro"=> $comprobante->numeroFile,
                "centro_costo"=> $this->centroCosto,
                "nro_pedido"=> "",
                "local"=> "",
                "caja"=> "",
                "cajero"=> "",
                "nro_transaccion"=> "",
                "orden_compra"=> $this->cod01,
                "glosa"=> $comprobante->numeroFile,
                "glosa_refe"=> "",
                "glosa_pie_pagina"=> $this->glosa,
                "mensaje"=> "",
                "numero_gr"=> "",
                "ant_numero"=> "",
                "docurela_numero"=> "",
                "ant_monto"=> "0.00",
                "op_exportacion"=> "0.00",
                "op_exonerada"=> 0.00,
                "op_inafecta"=> $comprobante->inafecto,
                "op_gravada"=> $comprobante->afecto,
                "tot_valorventa"=> $comprobante->afecto,
                "tot_precioventa"=> $comprobante->total,
                "isc"=> "0.00",
                "igv"=> $comprobante->igv,
                "porc_igv"=> "18.00",
                "igv_gratuita"=> "0.00",
                "importe_total"=> $comprobante->total,
                "total_pagar"=> $comprobante->total,
                "redondeo"=> "0.00",
                "total_otros_tributos"=> $comprobante->otrosImpuestos,
                "total_otros_cargos"=> 0,
                "cargodesc_motivo"=> "",
                "cargodesc_base"=> "0.00",
                "porc_dsctoglobal"=> "0.00",
                "total_descuento"=> 0,
                "descto_global"=> "0.00",
                "total_gratuitas"=> 0.00,
                "importe_letras"=> $comprobante->totalLetras,
                "total_icbper"=> "0.00",
                "usuario"=> "luis.quijano@hardnetconsulting.com",
                "tipocambio"=> $comprobante->tipoCambio,
                "codigo_sucu"=> "",
                "detraccion_bs"=> "",
                "detraccion_nrocta"=> "",
                "detraccion_porc"=> "",
                "detraccion_monto"=> "",
                "detraccion_moneda"=> "",
                "detraccion_mediopago"=> "",
                "almacen_id"=> null,
                "icoterms"=> "",
                "glosa_detraccion"=> $mensaje_detra
            ],
            "items" => [
                [
                    "tipodocu" => $comprobante->tipoDocumento,
                    "codigo" => "P00001",
                    "codigo_sunat" => "95101501",
                    "codigo_gs1" => "",
                    "descripcion" => $this->descripcion,
                    "cantidad" => "1.0000000000",
                    "unid" => "NIU",
                    "tipoprecioventa" => "01",
                    "tipo_afect_igv" => "10",
                    "codigo_tributo" => "1000",
                    "is_anticipo" => 0,
                    "valorunitbruto" => $comprobante->afecto,
                    "valorunit" => $comprobante->afecto,
                    "valorventabruto" => $comprobante->afecto,
                    "valorventa" => $comprobante->afecto,
                    "preciounitbruto" => $comprobante->total,
                    "preciounit" => $comprobante->total,
                    "precioventa" => $comprobante->total,
                    "precioventabruto" => $comprobante->total,
                    "igv" => $comprobante->igv,
                    "porc_igv" => "18.00",
                    "isc" => "0.00",
                    "porc_isc" => "0.00",
                    "dscto_unit" => "0.00",
                    "porc_dscto_unit" => "0.00",
                    "cod_cargodesc" => "",
                    "base_cargodesc" => "0.00",
                    "otrostributos_porc" => "0.00",
                    "otrostributos_monto" => $comprobante->otrosImpuestos,
                    "otrostributos_base" => "0.00",
                    "placavehiculo" => "",
                    "tot_impuesto" => "0.00",
                    "tipo_operacion" => "OP_GRAV",
                    "opt_tipodoi"  => "",
                    "opt_numerodoi"  => "",
                    "opt_pasaportepais"  => "",
                    "opt_huesped"  => "",
                    "opt_huespedpais"  => "",
                    "opt_fingresopais"  => "",
                    "opt_fcheckin"  => "",
                    "opt_fcheckout"  => "",
                    "opt_fconsumo" => "",
                    "opt_diaspermanencia" => "" 
                ]
            ]
        ];
        // DD($dataToSend);

        $funciones = new Funciones();
        $this->respSenda = $funciones->enviarCPE($dataToSend);

        return($dataToSend);
        // if ($file['type'] == 'success') {
        //     $doc = Documento::find($comprobante->id);
        //     $doc->respuestaSunat = $file['type'];
        //     $doc->save();

        // } else {
        //     session()->flash('error', 'Ocurrió un error enviando a Sunat');
        // }
        
    } 

    public function enviaCPEMixto($comprobante){
        
        $mensaje_detra = "";
        if($this->detraccion == 1){
            $mensaje_detra = "OPERACION SUJETA AL SISTEMA DE PAGO DE OBLIGACIONES TRIBUTARIAS CON EL GOBIERNO CENTRAL BANCO DE LA NACION: 00058327778";
        }

        // Datos a enviar en formato JSON
        $dataToSend = [
            "cabecera" => [
                "ruc_emisor" => "20604309027" ,
                "razonsocial_emisor"=> "AS TRAVEL PERU S.A.C",
                "direccion_emisor"=> "CAL.CALLE MARQUES DE MONTESCLAROS NRO. 165 DPTO. 104 URB. LA VIRREYNA LIMA - LIMA - SANTIAGO DE SURCO",
                "telefono_emisor"=> "(01) 972197067",
                "email_emisor"=> "facturaselectronicas@astravel.com.pe",
                "cod_domifiscal"=> "0000",
                "tiop_codi"=> "0101",
                "fecha"=> $comprobante->fechaEmision,
                "fvenc"=> $comprobante->fechaVencimiento,
                "tipodocu"=> $comprobante->tipoDocumento,
                "nro_serie_efact"=> $comprobante->serie,
                "tipo_moneda"=> $comprobante->moneda,
                "numero"=> str_pad($comprobante->numero,8,"0",STR_PAD_LEFT),
                "tipodocurefe"=> "",
                "numerorefe"=> "",
                "motivo_07_08"=> "",
                "descripcion_07_08"=> "",
                "fecharefe"=> "1900-01-01T00=>00=>00",
                "tipodoi"=> $this->codigoDocumentoIdentidad,
                "numerodoi"=> $comprobante->numeroDocumentoIdentidad,
                "desc_tipodocu"=> $this->descDocumentoIdentidad,
                "razonsocial"=> $comprobante->razonSocial,
                "direccion"=> $comprobante->direccionFiscal,
                "cliente"=> $comprobante->razonSocial,
                "email_cliente"=> "facturaselectronicas@astravel.com.pe",
                "email_cc"=> "",
                "codigo_cliente"=> $comprobante->idCliente,
                "rec_tele"=> $this->numeroTelefono,
                "rec_ubigeo"=> "",
                "rec_pais"=> "",
                "rec_depa"=> "",
                "rec_provi"=> "",
                "rec_distri"=> "",
                "rec_urb"=> "",
                "vendedor"=> "AS TRAVEL",
                "metodo_pago"=> $this->metodo_pago,
                "codigo_metodopago"=> $this->codigo_metodopago,
                "desc_metodopago"=> $this->desc_metodopago,
                "totalpagado_efectivo"=> "0.00",
                "vuelto"=> "0.00",
                "file_nro"=> $comprobante->numeroFile,
                "centro_costo"=> $this->centroCosto,
                "nro_pedido"=> "",
                "local"=> "",
                "caja"=> "",
                "cajero"=> "",
                "nro_transaccion"=> "",
                "orden_compra"=> $this->cod01,
                "glosa"=> $comprobante->numeroFile,
                "glosa_refe"=> "",
                "glosa_pie_pagina"=> $this->glosa,
                "mensaje"=> "",
                "numero_gr"=> "",
                "ant_numero"=> "",
                "docurela_numero"=> "",
                "ant_monto"=> "0.00",
                "op_exportacion"=> "0.00",
                "op_exonerada"=> 0.00,
                "op_inafecta"=> $comprobante->inafecto,
                "op_gravada"=> $comprobante->afecto,
                "tot_valorventa"=> $comprobante->afecto + $comprobante->inafecto,
                "tot_precioventa"=> $comprobante->total,
                "isc"=> "0.00",
                "igv"=> $comprobante->igv,
                "porc_igv"=> "18.00",
                "igv_gratuita"=> "0.00",
                "importe_total"=> $comprobante->total,
                "total_pagar"=> $comprobante->total,
                "redondeo"=> "0.00",
                "total_otros_tributos"=> $comprobante->otrosImpuestos,
                "total_otros_cargos"=> 0,
                "cargodesc_motivo"=> "",
                "cargodesc_base"=> "0.00",
                "porc_dsctoglobal"=> "0.00",
                "total_descuento"=> 0,
                "descto_global"=> "0.00",
                "total_gratuitas"=> 0,
                "importe_letras"=> $comprobante->totalLetras,
                "total_icbper"=> "0.00",
                "usuario"=> "luis.quijano@hardnetconsulting.com",
                "tipocambio"=> $comprobante->tipoCambio,
                "codigo_sucu"=> "",
                "detraccion_bs"=> "",
                "detraccion_nrocta"=> "",
                "detraccion_porc"=> "",
                "detraccion_monto"=> "",
                "detraccion_moneda"=> "",
                "detraccion_mediopago"=> "",
                "almacen_id"=> null,
                "icoterms"=> "",
                "glosa_detraccion"=> $mensaje_detra
            ],
            "items" => [
                [
                    "tipodocu" => $comprobante->tipoDocumento,
                    "codigo" => "P00001",
                    "codigo_sunat" => "95101501",
                    "codigo_gs1" => "",
                    "descripcion" => $this->descripcion,
                    "cantidad" => "1.0000000000",
                    "unid" => "NIU",
                    "tipoprecioventa" => "01",
                    "tipo_afect_igv" => "10",
                    "codigo_tributo" => "1000",
                    "is_anticipo" => 0,
                    "valorunitbruto" => $comprobante->afecto,
                    "valorunit" => $comprobante->afecto,
                    "valorventabruto" => $comprobante->afecto,
                    "valorventa" => $comprobante->afecto,
                    "preciounitbruto" => $comprobante->afecto + $comprobante->igv,//$comprobante->total,
                    "preciounit" => $comprobante->afecto + $comprobante->igv,//$comprobante->total,
                    "precioventa" => $comprobante->afecto + $comprobante->igv,//$comprobante->total,
                    "precioventabruto" => $comprobante->afecto + $comprobante->igv,//$comprobante->total,
                    "igv" => $comprobante->igv,
                    "porc_igv" => "18.00",
                    "isc" => "0.00",
                    "porc_isc" => "0.00",
                    "dscto_unit" => "0.00",
                    "porc_dscto_unit" => "0.00",
                    "cod_cargodesc" => "",
                    "base_cargodesc" => "0.00",
                    "otrostributos_porc" => "0.00",
                    "otrostributos_monto" => "0.00",
                    "otrostributos_base" => "0.00",
                    "placavehiculo" => "",
                    "tot_impuesto" => "0.00",
                    "tipo_operacion" => "OP_GRAV",
                    "opt_tipodoi"  => "",
                    "opt_numerodoi"  => "",
                    "opt_pasaportepais"  => "",
                    "opt_huesped"  => "",
                    "opt_huespedpais"  => "",
                    "opt_fingresopais"  => "",
                    "opt_fcheckin"  => "",
                    "opt_fcheckout"  => "",
                    "opt_fconsumo" => "",
                    "opt_diaspermanencia" => "" 
                ],
                [
                    "tipodocu" => $comprobante->tipoDocumento,
                    "codigo" => "P00001",
                    "codigo_sunat" => "95101501",
                    "codigo_gs1" => "",
                    "descripcion" => $this->descripcion,
                    "cantidad" => "1.0000000000",
                    "unid" => "NIU",
                    "tipoprecioventa" => "01",
                    "tipo_afect_igv" => "30",
                    "codigo_tributo" => "9998",
                    "is_anticipo" => 0,
                    "valorunitbruto" => $comprobante->inafecto,
                    "valorunit" => $comprobante->inafecto,
                    "valorventabruto" => $comprobante->inafecto,
                    "valorventa" => $comprobante->inafecto,
                    "preciounitbruto" => $comprobante->inafecto,//$comprobante->total,
                    "preciounit" => $comprobante->inafecto,//$comprobante->total,
                    "precioventa" => $comprobante->inafecto,//$comprobante->total,
                    "precioventabruto" => $comprobante->inafecto,//$comprobante->total,
                    "igv" => 0,
                    "porc_igv" => "18.00",
                    "isc" => "0.00",
                    "porc_isc" => "0.00",
                    "dscto_unit" => "0.00",
                    "porc_dscto_unit" => "0.00",
                    "cod_cargodesc" => "",
                    "base_cargodesc" => "0.00",
                    "otrostributos_porc" => "0.00",
                    "otrostributos_monto" => "0.00",
                    "otrostributos_base" => "0.00",
                    "placavehiculo" => "",
                    "tot_impuesto" => "0.00",
                    "tipo_operacion" => "OP_INA",
                    "opt_tipodoi"  => "",
                    "opt_numerodoi"  => "",
                    "opt_pasaportepais"  => "",
                    "opt_huesped"  => "",
                    "opt_huespedpais"  => "",
                    "opt_fingresopais"  => "",
                    "opt_fcheckin"  => "",
                    "opt_fcheckout"  => "",
                    "opt_fconsumo" => "",
                    "opt_diaspermanencia" => "" 
                ]
            ]
        ];
        // DD($dataToSend);

        $funciones = new Funciones();

        $this->respSenda = $funciones->enviarCPE($dataToSend);

        return($dataToSend);
        // $file = $funciones->enviarCPE($dataToSend);

        // if ($file['type'] == 'success') {
        //     $doc = Documento::find($comprobante->id);
        //     $doc->respuestaSunat = $file['type'];
        //     $doc->save();

        // } else {
        //     session()->flash('error', 'Ocurrió un error enviando a Sunat');
        // }
        
    } 

    public function enviaCPEInafecto($comprobante){
        
        $mensaje_detra = "";
        if($this->detraccion == 1){
            $mensaje_detra = "OPERACION SUJETA AL SISTEMA DE PAGO DE OBLIGACIONES TRIBUTARIAS CON EL GOBIERNO CENTRAL BANCO DE LA NACION: 00058327778";
        }

        // Datos a enviar en formato JSON
        $dataToSend = [
            "cabecera" => [
                "ruc_emisor" => "20604309027" ,
                "razonsocial_emisor"=> "AS TRAVEL PERU S.A.C",
                "direccion_emisor"=> "CAL.CALLE MARQUES DE MONTESCLAROS NRO. 165 DPTO. 104 URB. LA VIRREYNA LIMA - LIMA - SANTIAGO DE SURCO",
                "telefono_emisor"=> "(01) 972197067",
                "email_emisor"=> "facturaselectronicas@astravel.com.pe",
                "cod_domifiscal"=> "0000",
                "tiop_codi"=> "0101",
                "fecha"=> $comprobante->fechaEmision,
                "fvenc"=> $comprobante->fechaVencimiento,
                "tipodocu"=> $comprobante->tipoDocumento,
                "nro_serie_efact"=> $comprobante->serie,
                "tipo_moneda"=> $comprobante->moneda,
                "numero"=> str_pad($comprobante->numero,8,"0",STR_PAD_LEFT),
                "tipodocurefe"=> "",
                "numerorefe"=> "",
                "motivo_07_08"=> "",
                "descripcion_07_08"=> "",
                "fecharefe"=> "1900-01-01T00=>00=>00",
                "tipodoi"=> $this->codigoDocumentoIdentidad,
                "numerodoi"=> $comprobante->numeroDocumentoIdentidad,
                "desc_tipodocu"=> $this->descDocumentoIdentidad,
                "razonsocial"=> $comprobante->razonSocial,
                "direccion"=> $comprobante->direccionFiscal,
                "cliente"=> $comprobante->razonSocial,
                "email_cliente"=> "facturaselectronicas@astravel.com.pe",
                "email_cc"=> "",
                "codigo_cliente"=> $comprobante->idCliente,
                "rec_tele"=> $this->numeroTelefono,
                "rec_ubigeo"=> "",
                "rec_pais"=> "",
                "rec_depa"=> "",
                "rec_provi"=> "",
                "rec_distri"=> "",
                "rec_urb"=> "",
                "vendedor"=> "AS TRAVEL",
                "metodo_pago"=> $this->metodo_pago,
                "codigo_metodopago"=> $this->codigo_metodopago,
                "desc_metodopago"=> $this->desc_metodopago,
                "totalpagado_efectivo"=> "0.00",
                "vuelto"=> "0.00",
                "file_nro"=> $comprobante->numeroFile,
                "centro_costo"=> $this->centroCosto,
                "nro_pedido"=> "",
                "local"=> "",
                "caja"=> "",
                "cajero"=> "",
                "nro_transaccion"=> "",
                "orden_compra"=> $this->cod01,
                "glosa"=> $comprobante->numeroFile,
                "glosa_refe"=> "",
                "glosa_pie_pagina"=> $this->glosa,
                "mensaje"=> "",
                "numero_gr"=> "",
                "ant_numero"=> "",
                "docurela_numero"=> "",
                "ant_monto"=> "0.00",
                "op_exportacion"=> "0.00",
                "op_exonerada"=> 0.00,
                "op_inafecta"=> $comprobante->inafecto,
                "op_gravada"=> $comprobante->afecto,
                "tot_valorventa"=> $comprobante->afecto + $comprobante->inafecto,
                "tot_precioventa"=> $comprobante->total,
                "isc"=> "0.00",
                "igv"=> $comprobante->igv,
                "porc_igv"=> "18.00",
                "igv_gratuita"=> "0.00",
                "importe_total"=> $comprobante->total,
                "total_pagar"=> $comprobante->total,
                "redondeo"=> "0.00",
                "total_otros_tributos"=> $comprobante->otrosImpuestos,
                "total_otros_cargos"=> 0,
                "cargodesc_motivo"=> "",
                "cargodesc_base"=> "0.00",
                "porc_dsctoglobal"=> "0.00",
                "total_descuento"=> 0,
                "descto_global"=> "0.00",
                "total_gratuitas"=> 0,
                "importe_letras"=> $comprobante->totalLetras,
                "total_icbper"=> "0.00",
                "usuario"=> "luis.quijano@hardnetconsulting.com",
                "tipocambio"=> $comprobante->tipoCambio,
                "codigo_sucu"=> "",
                "detraccion_bs"=> "",
                "detraccion_nrocta"=> "",
                "detraccion_porc"=> "",
                "detraccion_monto"=> "",
                "detraccion_moneda"=> "",
                "detraccion_mediopago"=> "",
                "almacen_id"=> null,
                "icoterms"=> "",
                "glosa_detraccion"=> $mensaje_detra
            ],
            "items" => [
                [
                    "tipodocu" => $comprobante->tipoDocumento,
                    "codigo" => "P00001",
                    "codigo_sunat" => "95101501",
                    "codigo_gs1" => "",
                    "descripcion" => $this->descripcion,
                    "cantidad" => "1.0000000000",
                    "unid" => "NIU",
                    "tipoprecioventa" => "01",
                    "tipo_afect_igv" => "30",
                    "codigo_tributo" => "9998",
                    "is_anticipo" => 0,
                    "valorunitbruto" => $comprobante->inafecto,
                    "valorunit" => $comprobante->inafecto,
                    "valorventabruto" => $comprobante->inafecto,
                    "valorventa" => $comprobante->inafecto,
                    "preciounitbruto" => $comprobante->inafecto,//$comprobante->total,
                    "preciounit" => $comprobante->inafecto,//$comprobante->total,
                    "precioventa" => $comprobante->inafecto,//$comprobante->total,
                    "precioventabruto" => $comprobante->inafecto,//$comprobante->total,
                    "igv" => 0,
                    "porc_igv" => "18.00",
                    "isc" => "0.00",
                    "porc_isc" => "0.00",
                    "dscto_unit" => "0.00",
                    "porc_dscto_unit" => "0.00",
                    "cod_cargodesc" => "",
                    "base_cargodesc" => "0.00",
                    "otrostributos_porc" => "0.00",
                    "otrostributos_monto" => "0.00",
                    "otrostributos_base" => "0.00",
                    "placavehiculo" => "",
                    "tot_impuesto" => "0.00",
                    "tipo_operacion" => "OP_INA",
                    "opt_tipodoi"  => "",
                    "opt_numerodoi"  => "",
                    "opt_pasaportepais"  => "",
                    "opt_huesped"  => "",
                    "opt_huespedpais"  => "",
                    "opt_fingresopais"  => "",
                    "opt_fcheckin"  => "",
                    "opt_fcheckout"  => "",
                    "opt_fconsumo" => "",
                    "opt_diaspermanencia" => "" 
                ]
            ]
        ];
        // DD($dataToSend);

        $funciones = new Funciones();

        $this->respSenda = $funciones->enviarCPE($dataToSend);

        return($dataToSend);
        // $file = $funciones->enviarCPE($dataToSend);

        // if ($file['type'] == 'success') {
        //     $doc = Documento::find($comprobante->id);
        //     $doc->respuestaSunat = $file['type'];
        //     $doc->save();

        // } else {
        //     session()->flash('error', 'Ocurrió un error enviando a Sunat');
        // }
        
    } 

    public function enviaDCMixto($comprobante){
        $mensaje_detra = "";
        if($this->detraccion == 1){
            $mensaje_detra = "OPERACION SUJETA AL SISTEMA DE PAGO DE OBLIGACIONES TRIBUTARIAS CON EL GOBIERNO CENTRAL BANCO DE LA NACION: 00058327778";
        }

        // Datos a enviar en formato JSON
        $dataToSend = [
            "cabecera" => [
                "ruc_emisor" => "20604309027" ,
                "razonsocial_emisor"=> "AS TRAVEL PERU S.A.C",
                "direccion_emisor"=> "CAL.CALLE MARQUES DE MONTESCLAROS NRO. 165 DPTO. 104 URB. LA VIRREYNA LIMA - LIMA - SANTIAGO DE SURCO",
                "telefono_emisor"=> "(01) 972197067",
                "email_emisor"=> "facturaselectronicas@astravel.com.pe",
                "cod_domifiscal"=> "0000",
                "tiop_codi"=> "0101",
                "fecha"=> $comprobante->fechaEmision,
                "fvenci"=> $comprobante->fechaVencimiento,
                "tipodocu"=> $comprobante->tipoDocumento,
                "nro_serie_efact"=> $comprobante->serie,
                "tipo_moneda"=> $comprobante->moneda,
                "numero"=> str_pad($comprobante->numero,8,"0",STR_PAD_LEFT),
                "tipodocurefe"=> "",
                "numerorefe"=> "",
                "motivo_07_08"=> "",
                "descripcion_07_08"=> "",
                "fecharefe"=> "1900-01-01T00=>00=>00",
                "tipodoi"=> $this->codigoDocumentoIdentidad,
                "numerodoi"=> $comprobante->numeroDocumentoIdentidad,
                "desc_tipodocu"=> $this->descDocumentoIdentidad,
                "razonsocial"=> $comprobante->razonSocial,
                "direccion"=> $comprobante->direccionFiscal,
                "cliente"=> $comprobante->razonSocial,
                "email_cliente"=> "facturaselectronicas@astravel.com.pe",
                "email_cc"=> "",
                "codigo_cliente"=> $comprobante->idCliente,
                "rec_tele"=> $this->numeroTelefono,
                "rec_ubigeo"=> "",
                "rec_pais"=> "",
                "rec_depa"=> "",
                "rec_provi"=> "",
                "rec_distri"=> "",
                "rec_urb"=> "",
                "vendedor"=> "AS TRAVEL",
                "metodo_pago"=> $this->metodo_pago,
                "codigo_metodopago"=> $this->codigo_metodopago,
                "desc_metodopago"=> $this->desc_metodopago,
                "totalpagado_efectivo"=> "0.00",
                "vuelto"=> "0.00",
                "file_nro"=> $comprobante->numeroFile,
                "centro_costo"=> $this->centroCosto,
                "nro_pedido"=> "",
                "local"=> "",
                "caja"=> "",
                "cajero"=> "",
                "nro_transaccion"=> "",
                "orden_compra"=> "",
                // "glosa"=> $comprobante->numeroFile,
                "glosa"=> "",
                "glosa_refe"=> "",
                // "glosa_pie_pagina"=> $this->glosa,
                "glosa_pie_pagina"=> "",
                "mensaje"=> "",
                "numero_gr"=> "",
                "ant_numero"=> "",
                "docurela_numero"=> "",
                "ant_monto"=> "0.00",
                "op_exportacion"=> "0.00",
                "op_exonerada"=> 0.00,
                "op_inafecta"=> $comprobante->inafecto,
                "op_gravada"=> $comprobante->afecto,
                "tot_valorventa"=> $comprobante->afecto + $comprobante->inafecto,
                "tot_precioventa"=> $comprobante->total,
                "isc"=> "0.00",
                "igv"=> $comprobante->igv,
                "porc_igv"=> "18.00",
                "igv_gratuita"=> "0.00",
                "importe_total"=> $comprobante->total,
                "total_pagar"=> $comprobante->total,
                "redondeo"=> "0.00",
                "total_otros_tributos"=> $comprobante->otrosImpuestos,
                "total_otros_cargos"=> 0,
                "cargodesc_motivo"=> "",
                "cargodesc_base"=> "0.00",
                "porc_dsctoglobal"=> "0.00",
                "total_descuento"=> 0,
                "descto_global"=> "0.00",
                "total_gratuitas"=> 0,
                "importe_letras"=> $comprobante->totalLetras,
                "total_icbper"=> "0.00",
                "usuario"=> "luis.quijano@hardnetconsulting.com",
                "tipocambio"=> $comprobante->tipoCambio,
                "codigo_sucu"=> "",
                "detraccion_bs"=> "",
                "detraccion_nrocta"=> "",
                "detraccion_porc"=> "",
                "detraccion_monto"=> "",
                "detraccion_moneda"=> "",
                "detraccion_mediopago"=> "",
                "almacen_id"=> null,
                "icoterms"=> "",
                // "glosa_detraccion"=> $mensaje_detra
                "glosa_detraccion"=> ""
            ],
            "items" => [
                [
                    "tipodocu" => $comprobante->tipoDocumento,
                    "codigo" => "P00001",
                    "codigo_sunat" => "95101501",
                    "codigo_gs1" => "",
                    "descripcion" => $comprobante->glosa,
                    "cantidad" => "1.0000000000",
                    "unid" => "NIU",
                    "tipoprecioventa" => "01",
                    "tipo_afect_igv" => "10",
                    "codigo_tributo" => "1000",
                    "is_anticipo" => 0,
                    "valorunitbruto" => $comprobante->afecto,
                    "valorunit" => $comprobante->afecto,
                    "valorventabruto" => $comprobante->afecto,
                    "valorventa" => $comprobante->afecto,
                    "preciounitbruto" => $comprobante->afecto + $comprobante->igv,//$comprobante->total,
                    "preciounit" => $comprobante->afecto + $comprobante->igv,//$comprobante->total,
                    "precioventa" => $comprobante->afecto + $comprobante->igv,//$comprobante->total,
                    "precioventabruto" => $comprobante->afecto + $comprobante->igv,//$comprobante->total,
                    "igv" => $comprobante->igv,
                    "porc_igv" => "18.00",
                    "isc" => "0.00",
                    "porc_isc" => "0.00",
                    "dscto_unit" => "0.00",
                    "porc_dscto_unit" => "0.00",
                    "cod_cargodesc" => "",
                    "base_cargodesc" => "0.00",
                    "otrostributos_porc" => "0.00",
                    "otrostributos_monto" => "0.00",
                    "otrostributos_base" => "0.00",
                    "placavehiculo" => "",
                    "tot_impuesto" => "0.00",
                    "tipo_operacion" => "OP_GRAV",
                    "opt_tipodoi"  => "",
                    "opt_numerodoi"  => "",
                    "opt_pasaportepais"  => "",
                    "opt_huesped"  => "",
                    "opt_huespedpais"  => "",
                    "opt_fingresopais"  => "",
                    "opt_fcheckin"  => "",
                    "opt_fcheckout"  => "",
                    "opt_fconsumo" => "",
                    "opt_diaspermanencia" => "" 
                ],
                [
                    "tipodocu" => $comprobante->tipoDocumento,
                    "codigo" => "P00001",
                    "codigo_sunat" => "95101501",
                    "codigo_gs1" => "",
                    "descripcion" => $this->descripcion,
                    "cantidad" => "1.0000000000",
                    "unid" => "NIU",
                    "tipoprecioventa" => "01",
                    "tipo_afect_igv" => "30",
                    "codigo_tributo" => "9998",
                    "is_anticipo" => 0,
                    "valorunitbruto" => $comprobante->inafecto,
                    "valorunit" => $comprobante->inafecto,
                    "valorventabruto" => $comprobante->inafecto,
                    "valorventa" => $comprobante->inafecto,
                    "preciounitbruto" => $comprobante->inafecto,//$comprobante->total,
                    "preciounit" => $comprobante->inafecto,//$comprobante->total,
                    "precioventa" => $comprobante->inafecto,//$comprobante->total,
                    "precioventabruto" => $comprobante->inafecto,//$comprobante->total,
                    "igv" => 0,
                    "porc_igv" => "18.00",
                    "isc" => "0.00",
                    "porc_isc" => "0.00",
                    "dscto_unit" => "0.00",
                    "porc_dscto_unit" => "0.00",
                    "cod_cargodesc" => "",
                    "base_cargodesc" => "0.00",
                    "otrostributos_porc" => "0.00",
                    "otrostributos_monto" => "0.00",
                    "otrostributos_base" => "0.00",
                    "placavehiculo" => "",
                    "tot_impuesto" => "0.00",
                    "tipo_operacion" => "OP_INA",
                    "opt_tipodoi"  => "",
                    "opt_numerodoi"  => "",
                    "opt_pasaportepais"  => "",
                    "opt_huesped"  => "",
                    "opt_huespedpais"  => "",
                    "opt_fingresopais"  => "",
                    "opt_fcheckin"  => "",
                    "opt_fcheckout"  => "",
                    "opt_fconsumo" => "",
                    "opt_diaspermanencia" => "" 
                ]
            ]
        ];
        // DD($dataToSend);

        $funciones = new Funciones();
        $this->respSenda = $funciones->enviarDC($dataToSend);

        return($dataToSend);
        // $file = $funciones->enviarDC($dataToSend);

        // if ($file['type'] == 'success') {
        //     $doc = Documento::find($comprobante->id);
        //     $doc->respuestaSunat = $file['type'];
        //     $doc->save();

        // } else {
        //     session()->flash('error', 'Ocurrió un error enviando a Sunat');
        // }
        
    }

    public function enviaDC($comprobante){
        $mensaje_detra = "";
        if($this->detraccion == 1){
            $mensaje_detra = "OPERACION SUJETA AL SISTEMA DE PAGO DE OBLIGACIONES TRIBUTARIAS CON EL GOBIERNO CENTRAL BANCO DE LA NACION: 00058327778";
        }

        // Datos a enviar en formato JSON
        $dataToSend = [
            "cabecera" => [
                "ruc_emisor" => "20604309027" ,
                "razonsocial_emisor"=> "AS TRAVEL PERU S.A.C",
                "direccion_emisor"=> "CAL.CALLE MARQUES DE MONTESCLAROS NRO. 165 DPTO. 104 URB. LA VIRREYNA LIMA - LIMA - SANTIAGO DE SURCO",
                "telefono_emisor"=> "(01) 972197067",
                "email_emisor"=> "facturaselectronicas@astravel.com.pe",
                "cod_domifiscal"=> "0000",
                "tiop_codi"=> "0101",
                "fecha"=> $comprobante->fechaEmision,
                "fvenci"=> $comprobante->fechaVencimiento,
                "tipodocu"=> $comprobante->tipoDocumento,
                "nro_serie_efact"=> $comprobante->serie,
                "tipo_moneda"=> $comprobante->moneda,
                "numero"=> str_pad($comprobante->numero,8,"0",STR_PAD_LEFT),
                "tipodocurefe"=> "",
                "numerorefe"=> "",
                "motivo_07_08"=> "",
                "descripcion_07_08"=> "",
                "fecharefe"=> "1900-01-01T00=>00=>00",
                "tipodoi"=> $this->codigoDocumentoIdentidad,
                "numerodoi"=> $comprobante->numeroDocumentoIdentidad,
                "desc_tipodocu"=> $this->descDocumentoIdentidad,
                "razonsocial"=> $comprobante->razonSocial,
                "direccion"=> $comprobante->direccionFiscal,
                "cliente"=> $comprobante->razonSocial,
                "email_cliente"=> "facturaselectronicas@astravel.com.pe",
                "email_cc"=> "",
                "codigo_cliente"=> $comprobante->idCliente,
                "rec_tele"=> $this->numeroTelefono,
                "rec_ubigeo"=> "",
                "rec_pais"=> "",
                "rec_depa"=> "",
                "rec_provi"=> "",
                "rec_distri"=> "",
                "rec_urb"=> "",
                "vendedor"=> "AS TRAVEL",
                "metodo_pago"=> $this->metodo_pago,
                "codigo_metodopago"=> $this->codigo_metodopago,
                "desc_metodopago"=> $this->desc_metodopago,
                "totalpagado_efectivo"=> "0.00",
                "vuelto"=> "0.00",
                "file_nro"=> $comprobante->numeroFile,
                "centro_costo"=> $this->centroCosto,
                "nro_pedido"=> "",
                "local"=> "",
                "caja"=> "",
                "cajero"=> "",
                "nro_transaccion"=> "",
                "orden_compra"=> "",
                // "glosa"=> $comprobante->numeroFile,
                "glosa"=> "",
                "glosa_refe"=> "",
                // "glosa_pie_pagina"=> $this->glosa,
                "glosa_pie_pagina"=> "",
                "mensaje"=> "",
                "numero_gr"=> "",
                "ant_numero"=> "",
                "docurela_numero"=> "",
                "ant_monto"=> "0.00",
                "op_exportacion"=> "0.00",
                "op_exonerada"=> 0.00,
                "op_inafecta"=> $comprobante->inafecto,
                "op_gravada"=> $comprobante->afecto,
                "tot_valorventa"=> $comprobante->afecto,
                "tot_precioventa"=> $comprobante->total,
                "isc"=> "0.00",
                "igv"=> $comprobante->igv,
                "porc_igv"=> "18.00",
                "igv_gratuita"=> "0.00",
                "importe_total"=> $comprobante->total,
                "total_pagar"=> $comprobante->total,
                "redondeo"=> "0.00",
                "total_otros_tributos"=> $comprobante->otrosImpuestos,
                "total_otros_cargos"=> 0,
                "cargodesc_motivo"=> "",
                "cargodesc_base"=> "0.00",
                "porc_dsctoglobal"=> "0.00",
                "total_descuento"=> 0,
                "descto_global"=> "0.00",
                "total_gratuitas"=> 0.00,
                "importe_letras"=> $comprobante->totalLetras,
                "total_icbper"=> "0.00",
                "usuario"=> "luis.quijano@hardnetconsulting.com",
                "tipocambio"=> $comprobante->tipoCambio,
                "codigo_sucu"=> "",
                "detraccion_bs"=> "",
                "detraccion_nrocta"=> "",
                "detraccion_porc"=> "",
                "detraccion_monto"=> "",
                "detraccion_moneda"=> "",
                "detraccion_mediopago"=> "",
                "almacen_id"=> null,
                "icoterms"=> "",
                // "glosa_detraccion"=> $mensaje_detra
                "glosa_detraccion"=> ""
            ],
            "items" => [
                [
                    "tipodocu" => $comprobante->tipoDocumento,
                    "codigo" => "P00001",
                    "codigo_sunat" => "95101501",
                    "codigo_gs1" => "",
                    "descripcion" => $comprobante->glosa,
                    "cantidad" => "1.0000000000",
                    "unid" => "NIU",
                    "tipoprecioventa" => "01",
                    "tipo_afect_igv" => "10",
                    "codigo_tributo" => "1000",
                    "is_anticipo" => 0,
                    "valorunitbruto" => $comprobante->afecto,
                    "valorunit" => $comprobante->afecto,
                    "valorventabruto" => $comprobante->afecto,
                    "valorventa" => $comprobante->afecto,
                    "preciounitbruto" => $comprobante->total,
                    "preciounit" => $comprobante->total,
                    "precioventa" => $comprobante->total,
                    "precioventabruto" => $comprobante->total,
                    "igv" => $comprobante->igv,
                    "porc_igv" => "18.00",
                    "isc" => "0.00",
                    "porc_isc" => "0.00",
                    "dscto_unit" => "0.00",
                    "porc_dscto_unit" => "0.00",
                    "cod_cargodesc" => "",
                    "base_cargodesc" => "0.00",
                    "otrostributos_porc" => "0.00",
                    "otrostributos_monto" => $comprobante->otrosImpuestos,
                    "otrostributos_base" => "0.00",
                    "placavehiculo" => "",
                    "tot_impuesto" => "0.00",
                    "tipo_operacion" => "OP_GRAV",
                    "opt_tipodoi"  => "",
                    "opt_numerodoi"  => "",
                    "opt_pasaportepais"  => "",
                    "opt_huesped"  => "",
                    "opt_huespedpais"  => "",
                    "opt_fingresopais"  => "",
                    "opt_fcheckin"  => "",
                    "opt_fcheckout"  => "",
                    "opt_fconsumo" => "",
                    "opt_diaspermanencia" => "" 
                ]
            ]
        ];
        // DD($dataToSend);

        $funciones = new Funciones();
        $this->respSenda = $funciones->enviarDC($dataToSend);

        return($dataToSend);

        // $funciones = new Funciones();
        // $file = $funciones->enviarDC($dataToSend);

        // if ($file['type'] == 'success') {
        //     $doc = Documento::find($comprobante->id);
        //     $doc->respuestaSunat = $file['type'];
        //     $doc->save();

        // } else {
        //     session()->flash('error', 'Ocurrió un error enviando a Sunat');
        // }
        
    }

    public function enviaCPEMixto3($comprobante){
        
        $mensaje_detra = "";
        if($this->detraccion == 1){
            $mensaje_detra = "OPERACION SUJETA AL SISTEMA DE PAGO DE OBLIGACIONES TRIBUTARIAS CON EL GOBIERNO CENTRAL BANCO DE LA NACION: 00058327778";
        }

        // Datos a enviar en formato JSON
        $dataToSend = [
            "cabecera" => [
                "ruc_emisor" => "20604309027" ,
                "razonsocial_emisor"=> "AS TRAVEL PERU S.A.C",
                "direccion_emisor"=> "CAL.CALLE MARQUES DE MONTESCLAROS NRO. 165 DPTO. 104 URB. LA VIRREYNA LIMA - LIMA - SANTIAGO DE SURCO",
                "telefono_emisor"=> "(01) 972197067",
                "email_emisor"=> "facturaselectronicas@astravel.com.pe",
                "cod_domifiscal"=> "0000",
                "tiop_codi"=> "0101",
                "fecha"=> $comprobante->fechaEmision,
                "fvenc"=> $comprobante->fechaVencimiento,
                "tipodocu"=> $comprobante->tipoDocumento,
                "nro_serie_efact"=> $comprobante->serie,
                "tipo_moneda"=> $comprobante->moneda,
                "numero"=> str_pad($comprobante->numero,8,"0",STR_PAD_LEFT),
                "tipodocurefe"=> "",
                "numerorefe"=> "",
                "motivo_07_08"=> "",
                "descripcion_07_08"=> "",
                "fecharefe"=> "1900-01-01T00=>00=>00",
                "tipodoi"=> $this->codigoDocumentoIdentidad,
                "numerodoi"=> $comprobante->numeroDocumentoIdentidad,
                "desc_tipodocu"=> $this->descDocumentoIdentidad,
                "razonsocial"=> $comprobante->razonSocial,
                "direccion"=> $comprobante->direccionFiscal,
                "cliente"=> $comprobante->razonSocial,
                "email_cliente"=> "facturaselectronicas@astravel.com.pe",
                "email_cc"=> "",
                "codigo_cliente"=> $comprobante->idCliente,
                "rec_tele"=> $this->numeroTelefono,
                "rec_ubigeo"=> "",
                "rec_pais"=> "",
                "rec_depa"=> "",
                "rec_provi"=> "",
                "rec_distri"=> "",
                "rec_urb"=> "",
                "vendedor"=> "AS TRAVEL",
                "metodo_pago"=> $this->metodo_pago,
                "codigo_metodopago"=> $this->codigo_metodopago,
                "desc_metodopago"=> $this->desc_metodopago,
                "totalpagado_efectivo"=> "0.00",
                "vuelto"=> "0.00",
                "file_nro"=> $comprobante->numeroFile,
                "centro_costo"=> $this->centroCosto,
                "nro_pedido"=> "",
                "local"=> "",
                "caja"=> "",
                "cajero"=> "",
                "nro_transaccion"=> "",
                "orden_compra"=> $this->cod01,
                "glosa"=> $comprobante->numeroFile,
                "glosa_refe"=> "",
                "glosa_pie_pagina"=> $this->glosa,
                "mensaje"=> "",
                "numero_gr"=> "",
                "ant_numero"=> "",
                "docurela_numero"=> "",
                "ant_monto"=> "0.00",
                "op_exportacion"=> "0.00",
                "op_exonerada"=> 0.00,
                "op_inafecta"=> round($comprobante->inafecto - $comprobante->otrosImpuestos,2),
                "op_gravada"=> $comprobante->afecto,
                "tot_valorventa"=> round($comprobante->afecto + ($comprobante->inafecto - $comprobante->otrosImpuestos),2),
                "tot_precioventa"=> $comprobante->total - $comprobante->otrosImpuestos,
                "isc"=> "0.00",
                "igv"=> $comprobante->igv,
                "porc_igv"=> "18.00",
                "igv_gratuita"=> "0.00",
                "importe_total"=> $comprobante->total - $comprobante->otrosImpuestos,
                "total_pagar"=> $comprobante->total - $comprobante->otrosImpuestos,
                "redondeo"=> "0.00",
                "total_otros_tributos"=> $comprobante->otrosImpuestos,
                "total_otros_cargos"=> 0,
                "cargodesc_motivo"=> "",
                "cargodesc_base"=> "0.00",
                "porc_dsctoglobal"=> "0.00",
                "total_descuento"=> 0,
                "descto_global"=> "0.00",
                "total_gratuitas"=> 0,
                "importe_letras"=> $comprobante->totalLetras,
                "total_icbper"=> "0.00",
                "usuario"=> "facturaselectronicas@astravel.com.pe",
                "tipocambio"=> $comprobante->tipoCambio,
                "codigo_sucu"=> "",
                "detraccion_bs"=> "",
                "detraccion_nrocta"=> "",
                "detraccion_porc"=> "",
                "detraccion_monto"=> "",
                "detraccion_moneda"=> "",
                "detraccion_mediopago"=> "",
                "almacen_id"=> null,
                "icoterms"=> "",
                "glosa_detraccion"=> $mensaje_detra
            ],
            "items" => [
                [
                    "tipodocu" => $comprobante->tipoDocumento,
                    "codigo" => "P00001",
                    "codigo_sunat" => "95101501",
                    "codigo_gs1" => "",
                    "descripcion" => $this->descripcion,
                    "cantidad" => "1.0000000000",
                    "unid" => "NIU",
                    "tipoprecioventa" => "01",
                    "tipo_afect_igv" => "10",
                    "codigo_tributo" => "1000",
                    "is_anticipo" => 0,
                    "valorunitbruto" => $comprobante->afecto,
                    "valorunit" => $comprobante->afecto,
                    "valorventabruto" => $comprobante->afecto,
                    "valorventa" => $comprobante->afecto,
                    "preciounitbruto" => $comprobante->afecto + $comprobante->igv,//$comprobante->total,
                    "preciounit" => $comprobante->afecto + $comprobante->igv,//$comprobante->total,
                    "precioventa" => $comprobante->afecto + $comprobante->igv,//$comprobante->total,
                    "precioventabruto" => $comprobante->afecto + $comprobante->igv,//$comprobante->total,
                    "igv" => $comprobante->igv,
                    "porc_igv" => "18.00",
                    "isc" => "0.00",
                    "porc_isc" => "0.00",
                    "dscto_unit" => "0.00",
                    "porc_dscto_unit" => "0.00",
                    "cod_cargodesc" => "",
                    "base_cargodesc" => "0.00",
                    "otrostributos_porc" => "0.00",
                    "otrostributos_monto" => "0.00",
                    "otrostributos_base" => "0.00",
                    "placavehiculo" => "",
                    "tot_impuesto" => "0.00",
                    "tipo_operacion" => "OP_GRAV",
                    "opt_tipodoi"  => "",
                    "opt_numerodoi"  => "",
                    "opt_pasaportepais"  => "",
                    "opt_huesped"  => "",
                    "opt_huespedpais"  => "",
                    "opt_fingresopais"  => "",
                    "opt_fcheckin"  => "",
                    "opt_fcheckout"  => "",
                    "opt_fconsumo" => "",
                    "opt_diaspermanencia" => "" 
                ],
                [
                    "tipodocu" => $comprobante->tipoDocumento,
                    "codigo" => "P00001",
                    "codigo_sunat" => "95101501",
                    "codigo_gs1" => "",
                    "descripcion" => $this->descripcion,
                    "cantidad" => "1.0000000000",
                    "unid" => "NIU",
                    "tipoprecioventa" => "01",
                    "tipo_afect_igv" => "30",
                    "codigo_tributo" => "9998",
                    "is_anticipo" => 0,
                    "valorunitbruto" => round($comprobante->inafecto - $comprobante->otrosImpuestos,2),
                    "valorunit" => round($comprobante->inafecto - $comprobante->otrosImpuestos,2),
                    "valorventabruto" => round($comprobante->inafecto - $comprobante->otrosImpuestos,2),
                    "valorventa" => round($comprobante->inafecto - $comprobante->otrosImpuestos,2),
                    "preciounitbruto" => $comprobante->inafecto,//$comprobante->total,
                    "preciounit" => $comprobante->inafecto,//$comprobante->total,
                    "precioventa" => $comprobante->inafecto,//$comprobante->total,
                    "precioventabruto" => $comprobante->inafecto,//$comprobante->total,
                    "igv" => 0,
                    "porc_igv" => "18.00",
                    "isc" => "0.00",
                    "porc_isc" => "0.00",
                    "dscto_unit" => "0.00",
                    "porc_dscto_unit" => "0.00",
                    "cod_cargodesc" => "",
                    "base_cargodesc" => "0.00",
                    "otrostributos_porc" => "100.00",
                    "otrostributos_monto" => $comprobante->otrosImpuestos,
                    "otrostributos_base" => $comprobante->otrosImpuestos,
                    "placavehiculo" => "",
                    "tot_impuesto" => "0.00",
                    "tipo_operacion" => "OP_INA",
                    "opt_tipodoi"  => "",
                    "opt_numerodoi"  => "",
                    "opt_pasaportepais"  => "",
                    "opt_huesped"  => "",
                    "opt_huespedpais"  => "",
                    "opt_fingresopais"  => "",
                    "opt_fcheckin"  => "",
                    "opt_fcheckout"  => "",
                    "opt_fconsumo" => "",
                    "opt_diaspermanencia" => "0" 
                ]
            ]
        ];
        // DD($dataToSend);

        $funciones = new Funciones();

        $this->respSenda = $funciones->enviarCPE($dataToSend);

        return($dataToSend);
        
        
    } 

    public function exportar(){
        return Excel::download(new FacservacExport($this->idCliente,$this->startDate,$this->endDate),'ServiciosFacturados.xlsx');
    }
}
