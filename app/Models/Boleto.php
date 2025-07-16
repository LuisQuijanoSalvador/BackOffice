<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boleto extends Model
{
    use HasFactory;

    protected $fillable = [
        'numeroBoleto',
        'numeroFile',
        'idCliente',
        'idSolicitante',
        'fechaEmision',
        'idCounter',
        'idTipoFacturacion',
        'idTipoDocumento',
        'idArea',
        'idVendedor',
        'idConsolidador',
        'codigoReserva',
        'fechaReserva',
        'idGds',
        'idTipoTicket',
        'tipoRuta',
        'tipoTarifa',
        'idAerolinea',
        'origen',
        'pasajero',
        'idTipoPasajero',
        'ruta',
        'destino',
        'idDocumento',
        'tipoCambio',
        'idMoneda',
        'tarifaNeta',
        'inafecto',
        'igv',
        'otrosImpuestos',
        'xm',
        'total',
        'totalOrigen',
        'porcentajeComision',
        'montoComision',
        'descuentoCorporativo',
        'codigoDescCorp',
        'tarifaNormal',
        'tarifaAlta',
        'tarifaBaja',
        'idTipoPagoConsolidador',
        'centroCosto',
        'cod1',
        'cod2',
        'cod3',
        'cod4',
        'observaciones',
        'jsonBoleto',
        'idFee',
        'estado',
        'usuarioCreacion',
        'usuarioModificacion'
    ];

    public function tEstado(){
        return $this->hasOne(Estado::class,'id','estado');
    }

    public function tCliente(){
        return $this->hasOne(Cliente::class,'id','idCliente');
    }

    public function tSolicitante(){
        return $this->hasOne(Solicitante::class,'id','idSolicitante');
    }

    public function tCounter(){
        return $this->hasOne(Counter::class,'id','idCounter');
    }

    public function tTipoFacturacion(){
        return $this->hasOne(TipoFacturacion::class,'id','idTipoFacturacion');
    }

    public function tTipoDocumento(){
        return $this->hasOne(TipoDocumento::class,'id','idTipoDocumento');
    }

    public function tArea(){
        return $this->hasOne(Area::class,'id','idArea');
    }

    public function tVendedor(){
        return $this->hasOne(Vendedor::class,'id','idVendedor');
    }

    public function tConsolidador(){
        return $this->hasOne(Proveedor::class,'id','idConsolidador');
    }

    public function tGds(){
        return $this->hasOne(Gds::class,'id','idGds');
    }

    public function tTipoTicket(){
        return $this->hasOne(TipoTicket::class,'id','idTipoTicket');
    }

    public function tAerolinea(){
        return $this->hasOne(Aerolinea::class,'id','idAerolinea');
    }

    public function tDocumento(){
        return $this->hasOne(Documento::class,'id','idDocumento');
    }

    public function tMoneda(){
        return $this->hasOne(moneda::class,'id','idMoneda');
    }

    public function tTipoPago(){
        return $this->hasOne(TipoPago::class,'id','idTipoPagoConsolidador');
    }

    public function tUsuarioCreacion(){
        return $this->hasOne(User::class,'id','usuarioCreacion');
    }

    public function tUsuarioModificacion(){
        return $this->hasOne(User::class,'id','usuarioModificacion');
    }
}
