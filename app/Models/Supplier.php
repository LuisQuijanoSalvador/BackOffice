<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'razonSocial',
        'direccionFiscal',
        'tipoDocumentoIdentidad',
        'numeroDocumentoIdentidad',
        'numeroTelefono',
        'correo',
        'estado'
    ];

    public function tEstado(){
        return $this->hasOne(Estado::class,'id','estado');
    }

    public function tTipoDocumentoIdentidad(){
        return $this->hasOne(TipoDocumentoIdentidad::class,'id','tipoDocumentoIdentidad');
    }
}
