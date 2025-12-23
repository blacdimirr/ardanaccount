<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->foreignId('objeto_gasto_id')
                ->nullable()
                ->after('parent')
                ->constrained('clasificador_objeto_gasto')
                ->nullOnDelete();

            $table->foreignId('fuente_financiamiento_id')
                ->nullable()
                ->after('objeto_gasto_id')
                ->constrained('fuentes_financiamiento')
                ->nullOnDelete();

            $table->foreignId('programa_id')
                ->nullable()
                ->after('fuente_financiamiento_id')
                ->constrained('programas')
                ->nullOnDelete();

            $table->foreignId('proyecto_id')
                ->nullable()
                ->after('programa_id')
                ->constrained('proyectos')
                ->nullOnDelete();
        });

        Schema::table('product_service_categories', function (Blueprint $table) {
            $table->foreignId('objeto_gasto_id')
                ->nullable()
                ->after('chart_account_id')
                ->constrained('clasificador_objeto_gasto')
                ->nullOnDelete();

            $table->foreignId('fuente_financiamiento_id')
                ->nullable()
                ->after('objeto_gasto_id')
                ->constrained('fuentes_financiamiento')
                ->nullOnDelete();

            $table->foreignId('programa_id')
                ->nullable()
                ->after('fuente_financiamiento_id')
                ->constrained('programas')
                ->nullOnDelete();

            $table->foreignId('proyecto_id')
                ->nullable()
                ->after('programa_id')
                ->constrained('proyectos')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropForeign(['objeto_gasto_id']);
            $table->dropForeign(['fuente_financiamiento_id']);
            $table->dropForeign(['programa_id']);
            $table->dropForeign(['proyecto_id']);

            $table->dropColumn([
                'objeto_gasto_id',
                'fuente_financiamiento_id',
                'programa_id',
                'proyecto_id',
            ]);
        });

        Schema::table('product_service_categories', function (Blueprint $table) {
            $table->dropForeign(['objeto_gasto_id']);
            $table->dropForeign(['fuente_financiamiento_id']);
            $table->dropForeign(['programa_id']);
            $table->dropForeign(['proyecto_id']);

            $table->dropColumn([
                'objeto_gasto_id',
                'fuente_financiamiento_id',
                'programa_id',
                'proyecto_id',
            ]);
        });
    }
};
