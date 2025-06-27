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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->integer('tipoDocumento');
            $table->string('serie');
            $table->string('numero');
            $table->integer('idProveedor');
            $table->integer('idCliente');
            $table->string('numeroFile')->nullable();
            $table->string('formaPago');
            $table->date('fechaEmision');
            $table->string('moneda');
            $table->decimal('subTotal');
            $table->decimal('inafecto');
            $table->decimal('igv');
            $table->decimal('total');
            $table->string('totalLetras');
            $table->string('observacion')->nullable();
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
        Schema::dropIfExists('compras');
    }
};
