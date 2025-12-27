<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReglasConciliacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reglas_conciliacion', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('usar_referencia')->default(false);
            $table->boolean('usar_monto')->default(true);
            $table->boolean('usar_fecha')->default(true);
            $table->decimal('tolerancia_monto', 15, 2)->default('0.00');
            $table->unsignedInteger('rango_dias')->default(0);
            $table->boolean('activo')->default(true);
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
        Schema::dropIfExists('reglas_conciliacion');
    }
}
