<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->string('numeroServicio');
            $table->string('numeroFile');
            $table->integer('idCliente');
            $table->integer('idSolicitante')->nullable();
            $table->date('fechaEmision');
            $table->integer('idCounter');
            $table->integer('idTipoFacturacion');
            $table->integer('idTipoDocumento');
            $table->integer('idArea');
            $table->integer('idVendedor');
            $table->integer('idProveedor')->nullable();
            $table->string('codigoReserva')->nullable();
            $table->date('fechaReserva');
            $table->date('fechaIn');
            $table->date('fechaOut');
            $table->integer('idGds')->nullable();
            $table->integer('idTipoServicio');
            $table->string('tipoRuta')->nullable();
            $table->string('tipoTarifa');
            $table->integer('idAerolinea')->nullable();
            $table->string('origen')->nullable();
            $table->string('pasajero');
            $table->string('idTipoPasajero')->nullable();
            $table->string('ruta')->nullable();
            $table->string('destino')->nullable();
            $table->bigInteger('idDocumento')->nullable();
            $table->decimal('tipoCambio');
            $table->integer('idMoneda');
            $table->decimal('tarifaNeta');
            $table->decimal('inafecto');
            $table->decimal('igv');
            $table->decimal('otrosImpuestos')->nullable();
            $table->decimal('xm')->nullable();
            $table->decimal('total');
            $table->boolean('detraccion')->default(false);
            $table->decimal('totalOrigen');
            $table->decimal('porcentajeComision')->nullable();
            $table->decimal('montoComision')->nullable();
            $table->decimal('descuentoCorporativo')->nullable();
            $table->string('codigoDescCorp')->nullable();
            $table->decimal('tarifaNormal')->nullable();
            $table->decimal('tarifaAlta')->nullable();
            $table->decimal('tarifaBaja')->nullable();
            $table->integer('idTipoPagoConsolidador')->nullable();
            $table->string('centroCosto')->nullable();
            $table->string('cod1')->nullable();
            $table->string('cod2')->nullable();
            $table->string('cod3')->nullable();
            $table->string('cod4')->nullable();
            $table->string('observaciones')->nullable();
            $table->integer('estado');
            $table->integer('usuarioCreacion');
            $table->integer('usuarioModificacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
