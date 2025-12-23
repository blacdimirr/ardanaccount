<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adjudicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_compra_id')->constrained('procesos_compra')->cascadeOnDelete();
            $table->foreignId('oferta_id')->constrained('ofertas')->cascadeOnDelete();
            $table->foreignId('partida_presupuestaria_id')->nullable()->constrained('product_service_categories');
            $table->decimal('monto_adjudicado', 15, 2);
            $table->date('fecha_adjudicacion');
            $table->string('estado')->default('aprobado');
            $table->boolean('compromiso_aplicado')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adjudicaciones');
    }
};
