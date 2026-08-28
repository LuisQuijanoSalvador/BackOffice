<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Egreso extends Model
{
    use HasFactory;

    protected $table = 'egresos';

    protected $fillable = [
        'idBanco',
        'moneda',
        'monto',
        'idDocumento',
        'idEstado',
        'idUsuarioCreacion',
        'idUsuarioModificacion',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function banco()
    {
        return $this->belongsTo(Banco::class, 'idBanco');
    }

    public function documento()
    {
        return $this->belongsTo(Compra::class, 'idDocumento');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'idEstado');
    }
}