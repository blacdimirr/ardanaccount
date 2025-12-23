<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->json('monto_comprometido')->nullable()->after('monto_pim');
            $table->json('monto_devengado')->nullable()->after('monto_comprometido');
            $table->json('monto_pagado')->nullable()->after('monto_devengado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn('monto_comprometido');
            $table->dropColumn('monto_devengado');
            $table->dropColumn('monto_pagado');
        });
    }
};
