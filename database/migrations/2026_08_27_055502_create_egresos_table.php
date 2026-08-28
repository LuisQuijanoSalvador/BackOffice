<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('egresos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idBanco'); // Relación con tabla bancos
            $table->string('moneda', 10)->default('PEN');
            $table->decimal('monto', 12, 2);
            $table->unsignedBigInteger('idDocumento'); // Relación con tabla compras
            $table->unsignedBigInteger('idEstado');
            $table->unsignedBigInteger('idUsuarioCreacion');
            $table->unsignedBigInteger('idUsuarioModificacion')->nullable();
            $table->timestamps();

            // Llaves foráneas (opcional pero recomendado)
            $table->foreign('idBanco')->references('id')->on('bancos');
            $table->foreign('idDocumento')->references('id')->on('compras');
            $table->foreign('idEstado')->references('id')->on('estados');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('egresos');
    }
};