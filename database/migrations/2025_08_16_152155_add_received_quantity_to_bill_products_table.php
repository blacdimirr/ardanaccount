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
        Schema::table('bill_products', function (Blueprint $table) {
            // Entero, no negativo, por defecto 0. (Quita ->after() si no usas MySQL)
            $table->unsignedInteger('received_quantity')->default(0)->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_products', function (Blueprint $table) {
            $table->dropColumn('received_quantity');
        });
    }
};
