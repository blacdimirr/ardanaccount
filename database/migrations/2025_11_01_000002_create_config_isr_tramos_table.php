<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigIsrTramosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_isr_tramos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('rango_desde', 15, 2)->default(0);
            $table->decimal('rango_hasta', 15, 2)->nullable();
            $table->decimal('tasa', 8, 4)->default(0);
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
        Schema::dropIfExists('config_isr_tramos');
    }
}
