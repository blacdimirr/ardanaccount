<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFondosRotatoriosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fondos_rotatorios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre');
            $table->decimal('monto_inicial', 15, 2)->default('0.0');
            $table->decimal('monto_disponible', 15, 2)->default('0.0');
            $table->unsignedBigInteger('cuenta_contable_id');
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
        Schema::dropIfExists('fondos_rotatorios');
    }
}
