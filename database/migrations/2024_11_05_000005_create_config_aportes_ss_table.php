<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigAportesSsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_aportes_ss', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('tss_empleador', 8, 4)->default(0);
            $table->decimal('tss_empleado', 8, 4)->default(0);
            $table->decimal('infotep_empleador', 8, 4)->default(0);
            $table->decimal('infotep_empleado', 8, 4)->default(0);
            $table->decimal('idoppril_empleador', 8, 4)->default(0);
            $table->decimal('idoppril_empleado', 8, 4)->default(0);
            $table->integer('created_by')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('config_aportes_ss');
    }
}
