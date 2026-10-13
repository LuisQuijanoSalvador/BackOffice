<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subdiario extends Model
{
    use HasFactory;
    protected $table = 'subdiarios';
    protected $fillable = ['codigo', 'descripcion', 'idEstado'];

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'idEstado');
    }
}
