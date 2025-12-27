<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstadoConciliacionToRecaudacionesAndPaymentsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recaudaciones', function (Blueprint $table) {
            $table->string('estado_conciliacion')->default('pendiente')->after('created_by');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('estado_conciliacion')->default('pendiente')->after('reference');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recaudaciones', function (Blueprint $table) {
            $table->dropColumn('estado_conciliacion');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('estado_conciliacion');
        });
    }
}
