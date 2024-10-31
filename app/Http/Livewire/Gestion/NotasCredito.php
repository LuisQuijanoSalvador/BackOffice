<?php

namespace App\Http\Livewire\Gestion;

use Livewire\Component;
use App\Models\Documento;
use App\Models\TipoDocumento;
use App\Models\motivoCredito;
use App\Models\TipoCambio;
use Carbon\Carbon;
use App\Clases\Funciones;
use App\Clases\modelonumero;
use App\Models\TipoDocumentoIdentidad;
use App\Models\Cliente;
use App\Models\Boleto;
use App\Models\Servicio;

class NotasCredito extends Component
{
    public $idTipoDocumento=1,$fechaEmision,$tipoCambio=0,$numeroDocumento,$glosa,$motivo=1,$monto=0,
            $monedaLetra, $tipoDocuRefe, $numeroRefe, $codMotivo, $descMotivo, $fechaRefe,
            $metodo_pago, $codigo_metodopago, $desc_metodopago,$codigoDocumentoIdentidad,$descDocumentoIdentidad,
            $numeroTelefono, $descripcion, $documento;
    protected $documentos;

    public function updatedfechaEmision($fechaEmision){
        // dd($fechaCambio);
        $tipoCambio = TipoCambio::where('fechaCambio',$fechaEmision)->first();
        if($tipoCambio){
            $this->tipoCambio = $tipoCambio->montoCambio;
        }else{
            $this->tipoCambio = 0.00;
        }
        $this->fechaReserva = $fechaEmision;
    }

    public function mount(){
        $fechaActual = Carbon::now();
        
        $this->fechaEmision = Carbon::parse($fechaActual)->format("Y-m-d");
    }

    public function render()
    {
        $tipoDocumentos = TipoDocumento::all()->sortBy('descripcion');
        $motivos = motivoCredito::all()->sortBy('descripcion');
        return view('livewire.gestion.notas-credito', compact('tipoDocumentos','motivos'));
    }

    public function buscar(){
        $this->documentos = Documento::where('numero',$this->numeroDocumento)
                                    ->where('idTipoDocumento',$this->idTipoDocumento)
                                    ->get();
        
    }

    public function emitir(){
        
        if($this->monto==0){
            session()->flash('error', 'Verificar el monto.');
            $this->buscar();
            return;
        }

        $this->documento = Documento::where('numero',$this->numeroDocumento)
            ->where('idTipoDocumento',$this->idTipoDocumento)
            ->first();
        
        $funciones = new Funciones();
        $numLetras = new modelonumero();

        $numComprobante = $funciones->numeroComprobante('NOTA DE CREDITO');
        
        if ($this->documento->moneda == 'USD') {
            $this->monedaLetra = 'DOLARES AMERICANOS';
        } elseif($this->documento->moneda == 'PEN'){
            $this->monedaLetra = 'SOLES';
        }
        $this->tipoDocuRefe = $this->documento->tipoDocumento;
        $this->numeroRefe = $this->documento->serie . str_pad($this->documento->numero,8,"0",STR_PAD_LEFT);
        $motivo = motivoCredito::find($this->motivo);
        $this->codMotivo = $motivo->codigo;
        $this->descMotivo = $motivo->descripcion;
        $this->fechaRefe = $this->documento->fechaEmision;

        $cliente = Cliente::find($this->documento->idCliente);
        $this->numeroTelefono = $cliente->numeroTelefono;

        $this->tipoDocumentoIdentidad = $this->documento->tCliente->tipoDocumentoIdentidad;
        $tipoDocId = TipoDocumentoIdentidad::find($this->tipoDocumentoIdentidad);
        $this->codigoDocumentoIdentidad = $tipoDocId->codigo;
        $this->descDocumentoIdentidad = $tipoDocId->descripcion;

        $servicio = Servicio::where('idDocumento',$this->documento->id)->first();
        if($servicio){
            $this->descripcion = $servicio->tTipoServicio->descripcion;
        }else{
            $this->descripcion = "";
        }
        

        $documento = new Documento();
        $totalLetras = $numLetras->numtoletras($this->monto,$this->monedaLetra);
        
        $documento->idCliente = $this->documento->idCliente;
        $documento->razonSocial = $this->documento->razonSocial;
        $documento->direccionFiscal = $this->documento->direccionFiscal;
        $documento->numeroDocumentoIdentidad = $this->documento->numeroDocumentoIdentidad;
        $documento->idTipoDocumento = 3;
        $documento->tipoDocumento = '07';
        $documento->serie = 'F001';
        $documento->numero = $numComprobante;
        $documento->idMoneda = $this->documento->idMoneda;
        $documento->moneda = $this->documento->moneda;
        $documento->fechaEmision = $this->fechaEmision;
        $documento->fechaVencimiento = $this->fechaEmision;
        $documento->detraccion = 0;
        $documento->afecto = $this->documento->afecto;
        $documento->inafecto = $this->documento->inafecto;
        $documento->exonerado = 0;
        $documento->igv = $this->documento->igv;
        $documento->otrosImpuestos = 0;
        $documento->total = $this->monto;
        $documento->totalLetras = $totalLetras;
        $documento->idMedioPago = 8;
        $documento->glosa = $this->glosa;
        $documento->numeroFile = $this->documento->numeroFile;
        $documento->tipoServicio = 1;
        $documento->documentoReferencia = $this->numeroRefe;
        $documento->idMotivoNC = $this->motivo;
        $documento->idMotivoND = 0;
        $documento->tipoCambio = $this->tipoCambio;
        $documento->idEstado = 1;
        $documento->usuarioCreacion = auth()->user()->id;
        $documento->usuarioModificacion = auth()->user()->id;

        if($documento->inafecto > 0 && $documento->afecto > 0 && $documento->otrosImpuestos == 0){
            $dataJson = $this->enviaCPEMixto($documento);
            $jsonDoc = json_encode($dataJson, JSON_PRETTY_PRINT);
            
        }else{
            $dataJson = $this->enviaCPE($documento);
            $jsonDoc = json_encode($dataJson, JSON_PRETTY_PRINT);
            
        }
        
        

        $documento->save();
        $funciones->grabarCorrelativo('NOTA DE CREDITO',$numComprobante);

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
        $this->numeroDocumento = "";
        $this->monto = 0;
        $this->glosa = "";
    }

    public function enviaCPE($comprobante){
        
        $mensaje_detra = "";
        
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
                "tipodocurefe"=> $this->tipoDocuRefe,
                "numerorefe"=> $this->numeroRefe,
                "motivo_07_08"=> $this->codMotivo,
                "descripcion_07_08"=> $this->descMotivo,
                "fecharefe"=> $this->fechaRefe,
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
                "metodo_pago"=> "EFECTIVO",
                "codigo_metodopago"=> "CON",
                "desc_metodopago"=> "",
                "totalpagado_efectivo"=> "0.00",
                "vuelto"=> "0.00",
                "file_nro"=> $comprobante->numeroFile,
                "centro_costo"=> "",
                "nro_pedido"=> "",
                "local"=> "",
                "caja"=> "",
                "cajero"=> "",
                "nro_transaccion"=> "",
                "orden_compra"=> "",
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
                "op_gravada"=> round($comprobante->afecto,2),
                "tot_valorventa"=> round($comprobante->afecto,2),
                "tot_precioventa"=> $comprobante->total,
                "isc"=> "0.00",
                "igv"=> round($comprobante->igv,2),
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
                "usuario"=> "FAP",
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
                    "valorunitbruto" => round($comprobante->afecto,2),
                    "valorunit" => round($comprobante->afecto,2),
                    "valorventabruto" => round($comprobante->afecto,2),
                    "valorventa" => round($comprobante->afecto,2),
                    "preciounitbruto" => $comprobante->total,
                    "preciounit" => $comprobante->total,
                    "precioventa" => $comprobante->total,
                    "precioventabruto" => $comprobante->total,
                    "igv" => round($comprobante->igv,2),
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
                "centro_costo"=> $comprobante->centroCosto,
                "nro_pedido"=> "",
                "local"=> "",
                "caja"=> "",
                "cajero"=> "",
                "nro_transaccion"=> "",
                "orden_compra"=> $comprobante->cod01,
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

}
