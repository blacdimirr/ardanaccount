<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisicion_id')->constrained('requisitions')->cascadeOnDelete();
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio');
            $table->string('estado')->default('en_proceso');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_processes');
    }
};
