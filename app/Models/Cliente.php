<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'razonSocial',
        'nombreComercial',
        'direccionFiscal',
        'direccionFacturacion',
        'tipoCliente',
        'tipoDocumentoIdentidad',
        'numeroDocumentoIdentidad',
        'numeroTelefono',
        'contactoComercial',
        'telefonoComercial',
        'correoComercial',
        'contactoCobranza',
        'telefonoCobranza',
        'correoCobranza',
        'montoCredito',
        'moneda',
        'diasCredito',
        'counter',
        'tipoDocumento',
        'vendedor',
        'comision',
        'area',
        'cobrador',
        'tipoFacturacion',
        'estado'
    ];

    public function tTipofacturacion(){
        return $this->hasOne(TipoFacturacion::class,'id','tipoFacturacion');
    }

    public function tCobrador(){
        return $this->hasOne(Cobrador::class,'id','cobrador');
    }

    public function tArea(){
        return $this->hasOne(Area::class,'id','area');
    }

    public function tVendedor(){
        return $this->hasOne(Vendedor::class,'id','vendedor');
    }

    public function tTipoDocumento(){
        return $this->hasOne(TipoDocumento::class,'id','tipoDocumento');
    }

    public function tCounter(){
        return $this->hasOne(Counter::class,'id','counter');
    }

    public function tTipoDocumentoIdentidad(){
        return $this->hasOne(TipoDocumentoIdentidad::class,'id','tipoDocumentoIdentidad');
    }

    public function tTipoCliente(){
        return $this->hasOne(TipoCliente::class,'id','tipoCliente');
    }

    public function tMoneda(){
        return $this->hasOne(moneda::class,'id','moneda');
    }

    public function tEstado(){
        return $this->hasOne(Estado::class,'id','estado');
    }

    public function cargos()
    {
        return $this->hasMany(Cargo::class, 'idCliente', 'id'); // Ajusta las llaves foráneas si es necesario
    }
}
