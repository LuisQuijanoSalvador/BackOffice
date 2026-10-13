<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('compras', function (Blueprint $table) {
        $table->unsignedBigInteger('idCuentaContable')->nullable()->after('estado'); // Ajusta el 'after' si prefieres
        $table->unsignedBigInteger('idSubdiario')->nullable()->after('idCuentaContable');

        // Agregamos las llaves foráneas
        $table->foreign('idCuentaContable')->references('id')->on('cuenta_contables')->onDelete('set null');
        $table->foreign('idSubdiario')->references('id')->on('subdiarios')->onDelete('set null');
    });
}

public function down(): void
{
    Schema::table('compras', function (Blueprint $table) {
        $table->dropForeign(['idCuentaContable']);
        $table->dropForeign(['idSubdiario']);
        $table->dropColumn(['idCuentaContable', 'idSubdiario']);
    });
}
};
