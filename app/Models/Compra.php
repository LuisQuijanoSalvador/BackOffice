<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipoDocumento',
        'serie',
        'numero',
        'idProveedor',
        'idCliente',
        'numeroFile',
        'formaPago',
        'fechaEmision',
        'moneda',
        'subTotal',
        'inafecto',
        'igv',
        'total',
        'totalLetras',
        'observacion',
        'estado',
        'usuarioCreacion',
        'usuarioModificacion',
    ];

    // **AÑADE ESTA LÍNEA** o modifica si ya tienes la propiedad $casts
    protected $casts = [
        'fechaEmision' => 'datetime',
    ];

    // Relaciones
    public function tipoDocumentoR()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipoDocumento');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'idProveedor');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idCliente');
    }

    public function estadoR()
    {
        return $this->belongsTo(Estado::class, 'estado');
    }

    public function usuarioCreador()
    {
        return $this->belongsTo(User::class, 'usuarioCreacion');
    }

    public function usuarioModificador()
    {
        return $this->belongsTo(User::class, 'usuarioModificacion');
    }
    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class, 'idCompra');
    }
}
