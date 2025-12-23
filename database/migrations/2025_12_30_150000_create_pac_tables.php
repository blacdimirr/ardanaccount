<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pac', function (Blueprint $table) {
            $table->id();
            $table->integer('anio');
            $table->string('descripcion')->nullable();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();
        });

        Schema::create('pac_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pac_id')->constrained('pac')->cascadeOnDelete();
            $table->string('descripcion');
            $table->foreignId('partida_presupuestaria_id')->constrained('product_service_categories');
            $table->foreignId('objeto_gasto_id')->constrained('clasificador_objeto_gasto');
            $table->foreignId('fuente_financiamiento_id')->constrained('fuentes_financiamiento');
            $table->string('tipo_procedimiento');
            $table->decimal('monto_estimado', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pac_items');
        Schema::dropIfExists('pac');
    }
};
