<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staging_pagos_emitidos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('source_month_folder');
            $table->string('source_file');
            $table->string('source_sheet');
            $table->integer('source_row_number');
            $table->longText('raw_json');
            $table->string('hash', 40)->unique();
            $table->string('status', 20)->default('NEW');
            $table->text('error_message')->nullable();
            $table->date('fecha')->nullable();
            $table->decimal('monto', 18, 2)->nullable();
            $table->string('referencia')->nullable();
            $table->string('suplidor')->nullable();
            $table->string('metodo')->nullable();
            $table->string('cheque')->nullable();
            $table->string('transferencia')->nullable();
            $table->string('libramiento')->nullable();
            $table->text('concepto')->nullable();
            $table->string('orden_compra')->nullable();
            $table->string('banco')->nullable();
            $table->timestamps();
            $table->index(['source_month_folder', 'source_sheet']);
        });

        Schema::create('staging_ordenes_compra', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('source_month_folder');
            $table->string('source_file');
            $table->string('source_sheet');
            $table->integer('source_row_number');
            $table->longText('raw_json');
            $table->string('hash', 40)->unique();
            $table->string('status', 20)->default('NEW');
            $table->text('error_message')->nullable();
            $table->date('fecha')->nullable();
            $table->decimal('monto', 18, 2)->nullable();
            $table->string('numero_oc')->nullable();
            $table->string('suplidor')->nullable();
            $table->text('detalle')->nullable();
            $table->string('estado')->nullable();
            $table->string('unidad')->nullable();
            $table->string('rubro')->nullable();
            $table->timestamps();
            $table->index(['source_month_folder', 'source_sheet']);
        });

        Schema::create('staging_ingresos_origen', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('source_month_folder');
            $table->string('source_file');
            $table->string('source_sheet');
            $table->integer('source_row_number');
            $table->longText('raw_json');
            $table->string('hash', 40)->unique();
            $table->string('status', 20)->default('NEW');
            $table->text('error_message')->nullable();
            $table->date('fecha')->nullable();
            $table->decimal('monto', 18, 2)->nullable();
            $table->string('origen')->nullable();
            $table->string('referencia')->nullable();
            $table->text('observacion')->nullable();
            $table->string('banco')->nullable();
            $table->timestamps();
            $table->index(['source_month_folder', 'source_sheet']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staging_ingresos_origen');
        Schema::dropIfExists('staging_ordenes_compra');
        Schema::dropIfExists('staging_pagos_emitidos');
    }
};
