<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {

            if (!Schema::hasColumn('assets', 'proveedor_id')) {
                 $table->unsignedBigInteger('proveedor_id')->nullable();

            // Asegúrate de que 'vender_id' en 'venders' tenga un índice único
            $table->foreign('proveedor_id')
                ->references('vender_id')
                ->on('venders')
                ->onDelete('set null');
            }

           
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['proveedor_id']);
            $table->dropColumn([
                'proveedor_id',
            ]);
        });
    }
};
