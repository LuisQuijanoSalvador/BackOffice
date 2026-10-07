<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuenta_contables', function (Blueprint $table) {
            $table->id();
            $table->string('cuenta', 20)->unique(); 
            $table->string('descripcion', 255);
            $table->unsignedBigInteger('usuarioCreacion');
            $table->unsignedBigInteger('usuarioModificacion')->nullable();
            $table->unsignedBigInteger('idEstado');
            $table->timestamps();

            // Llaves foráneas
            $table->foreign('idEstado')->references('id')->on('estados')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuenta_contables');
    }
};