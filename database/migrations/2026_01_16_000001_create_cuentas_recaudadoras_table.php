<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuentasRecaudadorasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cuentas_recaudadoras', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('banco');
            $table->string('numero_cuenta');
            $table->string('tipo');
            $table->boolean('activo')->default(true);
            $table->integer('created_by')->default('0');
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
        Schema::dropIfExists('cuentas_recaudadoras');
    }
}
