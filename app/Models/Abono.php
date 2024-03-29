<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abono extends Model
{
    use HasFactory;

    protected $fillable = [
        'idCargo',
        'fechaAbono',
        'numeroAbono',
        'monto',
        'moneda',
        'tipoCambio',
        'idMedioPago',
        'referencia',
        'idBanco',
        'numeroCuenta',
        'idTarjetaCredito',
        'observaciones',
        'idEstado',
        'usuarioCreacion',
        'usuarioModificacion'
    ];

    public function tEstado(){
        return $this->hasOne(Estado::class,'id','idEstado');
    }

    public function tCargo(){
        return $this->hasOne(Cargo::class,'id','idCargo');
    }

    public function tMedioPago(){
        return $this->hasOne(MedioPago::class,'id','idMedioPago');
    }

    public function tBanco(){
        return $this->hasOne(Banco::class,'id','idBanco');
    }

    public function tTarjetaCredito(){
        return $this->hasOne(TarjetaCredito::class,'id','idTarjetaCredito');
    }
}
