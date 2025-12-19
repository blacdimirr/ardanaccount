<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRetentionCertificatesTable extends Migration
{
    public function up(): void
    {
        Schema::create('retention_certificates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('vender_id')->nullable();
            $table->string('supplier_type', 100)->nullable();
            $table->decimal('itbis_amount', 15, 2)->default(0);
            $table->decimal('isr_amount', 15, 2)->default(0);
            $table->date('issued_at')->nullable();
            $table->integer('created_by')->default(0);
            $table->timestamps();

            $table->foreign('bill_id')->references('id')->on('bills')->cascadeOnDelete();
            $table->foreign('vender_id')->references('id')->on('venders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retention_certificates');
    }
}
