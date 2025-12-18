<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
   public function up(): void
    {
        // 1) Agregar columna y FK
        Schema::table('bill_products', function (Blueprint $table) {
            // La ponemos cerca de product_id para orden lógico
            $table->unsignedBigInteger('category_id')->nullable()->after('product_id');

            $table->foreign('category_id')
                ->references('id')->on('product_service_categories')
                ->nullOnDelete(); // si borran la categoría, deja null
        });

        // 2) Backfill: tomar category_id desde el producto
        // Opción A (portátil entre motores): actualizar por lotes en PHP
        $rows = DB::table('bill_products')
            ->whereNull('category_id')
            ->select('id', 'product_id')
            ->get();

        if ($rows->isNotEmpty()) {
            // mapa: product_id => category_id
            $map = DB::table('product_services')
                ->pluck('category_id', 'id'); // [product_id => category_id]

            foreach ($rows as $row) {
                $catId = $map[$row->product_id] ?? null;
                if (!empty($catId)) {
                    DB::table('bill_products')
                        ->where('id', $row->id)
                        ->update(['category_id' => $catId]);
                }
            }
        }

        // --- Opción B (MySQL): más rápida, comentar A y descomentar B si quieres ---
        // DB::statement('
        //     UPDATE bill_products bp
        //     JOIN product_services ps ON ps.id = bp.product_id
        //     SET bp.category_id = ps.category_id
        //     WHERE bp.category_id IS NULL
        // ');
    }

    public function down(): void
    {
        Schema::table('bill_products', function (Blueprint $table) {
            // Primero soltar la FK y luego la columna
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
