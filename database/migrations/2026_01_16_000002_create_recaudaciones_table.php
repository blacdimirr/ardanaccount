<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecaudacionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recaudaciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('fecha');
            $table->string('servicio');
            $table->decimal('monto', 15, 2)->default('0.0');
            $table->string('metodo_pago');
            $table->unsignedBigInteger('cuenta_recaudadora_id');
            $table->unsignedBigInteger('paciente_id')->nullable();
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
        Schema::dropIfExists('recaudaciones');
    }
}
