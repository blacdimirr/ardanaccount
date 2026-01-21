<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historico_accounting_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('document_type');
            $table->string('match_field');
            $table->string('match_type')->default('contains');
            $table->text('match_value');
            $table->string('debit_account_code')->nullable();
            $table->string('credit_account_code')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['document_type', 'match_field']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_accounting_rules');
    }
};
