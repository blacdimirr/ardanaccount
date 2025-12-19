<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRetentionRulesTable extends Migration
{
    public function up(): void
    {
        Schema::create('retention_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('supplier_type', 100)->nullable();
            $table->unsignedBigInteger('service_category_id')->nullable();
            $table->decimal('itbis_retention_rate', 5, 2)->default(0);
            $table->decimal('isr_retention_rate', 5, 2)->default(0);
            $table->integer('created_by')->default(0);
            $table->timestamps();

            $table->foreign('service_category_id')->references('id')->on('product_service_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retention_rules');
    }
}
