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
        Schema::create('abono_pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idCargoPorPagar'); // Relación con la deuda
            $table->unsignedBigInteger('idEgreso');         // Relación con la salida de dinero
            $table->string('moneda', 10)->default('PEN');
            $table->decimal('monto', 12, 2);
            $table->date('fechaPago');
            $table->unsignedBigInteger('idBanco');
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('idEstado');         // Para manejar Activo / Anulado
            $table->unsignedBigInteger('idUsuarioCreacion');
            $table->unsignedBigInteger('idUsuarioModificacion')->nullable();
            $table->timestamps();

            $table->foreign('idCargoPorPagar')->references('id')->on('cargo_por_pagars');
            $table->foreign('idEgreso')->references('id')->on('egresos');
            $table->foreign('idBanco')->references('id')->on('bancos');
            $table->foreign('idEstado')->references('id')->on('estados');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abono_pagos');
    }
};
