<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_estados_financieros', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_nota', 50);
            $table->string('titulo');
            $table->text('contenido');
            $table->date('periodo');
            $table->boolean('estado')->default(true);
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();

            $table->index(['created_by', 'periodo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_estados_financieros');
    }
};
