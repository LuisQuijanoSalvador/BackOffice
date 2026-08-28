<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoPorPagar extends Model
{
    use HasFactory;

    protected $table = 'cargo_por_pagars';

    protected $fillable = [
        'idDocumento',
        'idProveedor',
        'montoCredito',
        'diasCredito',
        'fechaEmision',
        'fechaVencimiento',
        'moneda',
        'tarifaNeta',
        'inafecto',
        'igv',
        'otrosImpuestos',
        'total',
        'tipoDocumento',
        'serieDocumento',
        'numeroDocumento',
        'montoCargo',
        'tipoCambio',
        'saldo',
        'idEstado',
        'usuarioCreacion',
        'usuarioModificacion',
    ];

    protected $casts = [
        'fechaEmision' => 'date',
        'fechaVencimiento' => 'date',
        'montoCredito' => 'decimal:2',
        'tarifaNeta' => 'decimal:2',
        'inafecto' => 'decimal:2',
        'igv' => 'decimal:2',
        'otrosImpuestos' => 'decimal:2',
        'total' => 'decimal:2',
        'montoCargo' => 'decimal:2',
        'tipoCambio' => 'decimal:2',
        'saldo' => 'decimal:2',
    ];

    // Relación con el documento (Compra)
    public function documento()
    {
        return $this->belongsTo(Compra::class, 'idDocumento');
    }

    // Relación con el proveedor
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'idProveedor');
    }

    // Relación con el estado
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'idEstado');
    }
}