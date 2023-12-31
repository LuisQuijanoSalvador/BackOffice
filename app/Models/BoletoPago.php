<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoletoPago extends Model
{
    use HasFactory;

    protected $fillable = [
        'idBoleto',
        'idMedioPago',
        'idTarjetaCredito',
        'numeroTarjeta',
        'monto',
        'fechaVencimientoTC',
        'idEstado',
        'usuarioCreacion',
        'usuarioModificacion'
    ];

    public function tEstado(){
        return $this->hasOne(Estado::class,'id','idEstado');
    }

    public function tBoleto(){
        return $this->hasOne(Boleto::class,'id','idBoleto');
    }

    public function tMedioPago(){
        return $this->hasOne(MedioPago::class,'id','idMedioPago');
    }

    public function tTarjetaCredito(){
        return $this->hasOne(TarjetaCredito::class,'id','idTarjetaCredito');
    }
}
