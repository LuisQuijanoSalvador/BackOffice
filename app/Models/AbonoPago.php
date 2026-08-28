<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbonoPago extends Model
{
    use HasFactory;
    protected $table = 'abono_pagos';

    protected $fillable = [
        'idCargoPorPagar', 'idEgreso', 'moneda', 'monto', 'fechaPago',
        'idBanco', 'observacion', 'idEstado', 'idUsuarioCreacion', 'idUsuarioModificacion'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fechaPago' => 'date',
    ];

    public function cargo() { return $this->belongsTo(CargoPorPagar::class, 'idCargoPorPagar'); }
    public function egreso() { return $this->belongsTo(Egreso::class, 'idEgreso'); }
    public function banco()  { return $this->belongsTo(Banco::class, 'idBanco'); }
    public function estado() { return $this->belongsTo(Estado::class, 'idEstado'); }
}
