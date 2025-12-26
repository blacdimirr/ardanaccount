<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPeriodoToNominaConceptosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nomina_conceptos', function (Blueprint $table) {
            $table->unsignedBigInteger('nomina_periodo_id')->nullable()->after('aplica_tss');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nomina_conceptos', function (Blueprint $table) {
            $table->dropColumn('nomina_periodo_id');
        });
    }
}
