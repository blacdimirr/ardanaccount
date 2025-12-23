<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clasificador_objeto_gasto', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('description');
            $table->unsignedInteger('level');
            $table->boolean('active')->default(true);
            $table->integer('created_by')->default(0);
            $table->timestamps();
        });

        Schema::create('fuentes_financiamiento', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('description');
            $table->boolean('active')->default(true);
            $table->integer('created_by')->default(0);
            $table->timestamps();
        });

        Schema::create('programas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->integer('created_by')->default(0);
            $table->timestamps();
        });

        Schema::create('proyectos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('name');
            $table->unsignedBigInteger('program_id');
            $table->boolean('active')->default(true);
            $table->integer('created_by')->default(0);
            $table->timestamps();

            $table->foreign('program_id')
                ->references('id')
                ->on('programas')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyectos');
        Schema::dropIfExists('programas');
        Schema::dropIfExists('fuentes_financiamiento');
        Schema::dropIfExists('clasificador_objeto_gasto');
    }
};
