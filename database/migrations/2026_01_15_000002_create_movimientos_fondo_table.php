<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimientosFondoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movimientos_fondo', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('fondo_id');
            $table->string('tipo');
            $table->decimal('monto', 15, 2)->default('0.0');
            $table->date('fecha');
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('comprobante_id')->nullable();
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
        Schema::dropIfExists('movimientos_fondo');
    }
}
