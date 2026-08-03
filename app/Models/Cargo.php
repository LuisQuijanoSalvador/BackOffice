<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Cargo extends Model
{
    use HasFactory;
    protected $table = 'cargos';

    protected $fillable = [
        'idDocumento',
        'idCliente',
        'idCobrador',
        'idCounter',
        'idAerolinea',
        'idProveedor',
        'idSolicitante',
        'idBoleto',
        'idServicio',
        'montoCredito',
        'diasCredito',
        'fechaEmision',
        'fechaVencimiento',
        'numeroBoleto',
        'pasajero',
        'tipoRuta',
        'ruta',
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
        'montoCargo' => 'decimal:2',
        'saldo' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function tEstado(){
        return $this->hasOne(Estado::class,'id','idEstado');
    }

    public function tDocumento(){
        return $this->hasOne(Documento::class,'id','idDocumento');
    }

    public function tCliente(){
        return $this->hasOne(Cliente::class,'id','idCliente');
    }

    public function tCobrador(){
        return $this->hasOne(Cobrador::class,'id','idCobrador');
    }

    public function tCounter(){
        return $this->hasOne(Counter::class,'id','idCounter');
    }

    public function tAerolinea(){
        return $this->hasOne(Aerolinea::class,'id','idAerolinea');
    }

    public function tProveedor(){
        return $this->hasOne(Proveedor::class,'id','idProveedor');
    }

    public function tBoleto(){
        return $this->hasOne(Boleto::class,'id','idBoleto');
    }

    public function tServicio(){
        return $this->hasOne(Servicio::class,'id','idServicio');
    }

    public function tSolicitante(){
        return $this->hasOne(Solicitante::class,'id','idSolicitante');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'idCliente');
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'idDocumento');
    }

    public function abonos()
    {
        return $this->hasMany(Abono::class, 'idCargo');
    }

    // Calcular días de atraso
    public function getDiasAtrasoAttribute()
    {
        if ($this->saldo <= 0) {
            return 0;
        }

        $hoy = Carbon::today();
        $fechaVencimiento = Carbon::parse($this->fechaVencimiento);

        if ($hoy->gt($fechaVencimiento)) {
            return $hoy->diffInDays($fechaVencimiento);
        }

        return 0;
    }

    // Verificar si está vencido
    public function getEstaVencidoAttribute()
    {
        return $this->saldo > 0 && $this->diasAtraso > 0;
    }

    // Calcular deuda vencida
    public function getDeudaVencidaAttribute()
    {
        return $this->estaVencido ? $this->saldo : 0;
    }

}
