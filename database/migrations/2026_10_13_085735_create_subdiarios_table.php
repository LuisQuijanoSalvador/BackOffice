<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('subdiarios', function (Blueprint $table) {
        $table->id();
        $table->string('codigo', 20)->unique();
        $table->string('descripcion', 255);
        $table->unsignedBigInteger('idEstado');
        $table->timestamps();

        $table->foreign('idEstado')->references('id')->on('estados')->onDelete('restrict');
    });
}
public function down(): void { Schema::dropIfExists('subdiarios'); }
};
