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
        Schema::table('compras', function (Blueprint $table) {
            // Agregamos el campo montoDetraccion. 
            // Lo colocamos después del campo 'igv' (puedes cambiar 'igv' por 'total' si prefieres)
            $table->decimal('montoDetraccion', 12, 2)->nullable()->default(0)->after('igv');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropColumn('montoDetraccion');
        });
    }
};