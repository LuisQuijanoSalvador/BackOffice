<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VistaReporteAbono extends Model
{
    use HasFactory;

    protected $table = 'vista_reporte_abonos'; // Indicar el nombre de la vista
    protected $primaryKey = null; // Las vistas no siempre tienen una PK explícita o se puede inferir
    public $incrementing = false; // Las vistas no suelen ser auto-incrementales
    public $timestamps = false; // Las vistas no suelen tener timestamps

    
}