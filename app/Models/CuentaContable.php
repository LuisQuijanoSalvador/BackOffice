<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuentaContable extends Model
{
    use HasFactory;

    // Definimos explícitamente la tabla siguiendo la convención de Laravel
    protected $table = 'cuenta_contables';

    protected $fillable = [
        'cuenta',
        'descripcion',
        'usuarioCreacion',
        'usuarioModificacion',
        'idEstado',
    ];

    // Relaciones
    public function userCreacion()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuarioCreacion');
    }

    public function userModificacion()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuarioModificacion');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'idEstado');
    }
}