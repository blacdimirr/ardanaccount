<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNcfSeriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('ncf_series', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ncf_type_id');
            $table->string('series')->nullable();
            $table->unsignedBigInteger('start_number');
            $table->unsignedBigInteger('end_number');
            $table->unsignedBigInteger('current_number')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->enum('status', ['activo', 'vencido', 'agotado'])->default('activo');
            $table->integer('created_by')->default(0);
            $table->timestamps();

            $table->foreign('ncf_type_id')->references('id')->on('ncf_types')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ncf_series');
    }
}
