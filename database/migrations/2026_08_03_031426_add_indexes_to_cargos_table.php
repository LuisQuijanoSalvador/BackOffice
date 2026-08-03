<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToCargos extends Migration
{
    public function up()
    {
        Schema::table('cargos', function (Blueprint $table) {
            $table->index(['idCliente', 'idEstado', 'saldo']);
            $table->index(['fechaEmision', 'fechaVencimiento']);
            $table->index(['moneda', 'idEstado']);
        });
    }

    public function down()
    {
        Schema::table('cargos', function (Blueprint $table) {
            $table->dropIndex(['idCliente', 'idEstado', 'saldo']);
            $table->dropIndex(['fechaEmision', 'fechaVencimiento']);
            $table->dropIndex(['moneda', 'idEstado']);
        });
    }
}