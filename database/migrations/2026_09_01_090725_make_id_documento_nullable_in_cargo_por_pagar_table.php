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
        Schema::table('cargo_por_pagars', function (Blueprint $table) {
            $table->integer('idDocumento')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargo_por_pagars', function (Blueprint $table) {
            $table->integer('idDocumento')->nullable(false)->change();
        });
    }
};
