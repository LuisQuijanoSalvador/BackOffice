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
        Schema::create('cargo_por_pagars', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('idDocumento');
            $table->integer('idProveedor');
            $table->decimal('montoCredito', 8, 2);
            $table->integer('diasCredito');
            $table->date('fechaEmision');
            $table->date('fechaVencimiento');
            $table->string('moneda', 255);
            $table->decimal('tarifaNeta', 8, 2);
            $table->decimal('inafecto', 8, 2);
            $table->decimal('igv', 8, 2);
            $table->decimal('otrosImpuestos', 8, 2);
            $table->decimal('total', 8, 2);
            $table->string('tipoDocumento', 255);
            $table->string('serieDocumento', 50);
            $table->string('numeroDocumento', 255);
            $table->decimal('montoCargo', 8, 2);
            $table->decimal('tipoCambio', 8, 2);
            $table->decimal('saldo', 8, 2);
            $table->integer('idEstado');
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
        Schema::dropIfExists('cargo_por_pagars');
    }
};
