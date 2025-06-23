<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'idCompra',
        'cantidad',
        'unidadMedida',
        'descripcion',
        'valorUnitario',
        'estado'
    ];

    public function tEstado(){
        return $this->hasOne(Estado::class,'id','estado');
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'idCompra');
    }
}
