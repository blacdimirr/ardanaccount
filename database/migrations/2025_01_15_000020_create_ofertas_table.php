<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ofertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_compra_id')->constrained('procesos_compra')->cascadeOnDelete();
            $table->string('proveedor');
            $table->decimal('monto_ofertado', 15, 2);
            $table->date('fecha_oferta');
            $table->string('estado')->default('presentada');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ofertas');
    }
};
