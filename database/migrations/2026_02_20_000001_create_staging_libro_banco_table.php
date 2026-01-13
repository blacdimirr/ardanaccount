<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staging_libro_banco', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('migration_batch_id')->nullable();
            $table->string('source_month_folder');
            $table->string('source_file');
            $table->string('source_sheet');
            $table->integer('source_row_number');
            $table->longText('raw_json');
            $table->string('hash', 40)->unique();
            $table->string('status', 20)->default('NEW');
            $table->text('error_message')->nullable();
            $table->date('txn_date')->nullable();
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->decimal('balance', 18, 2)->nullable();
            $table->unsignedBigInteger('cuenta_recaudadora_id')->nullable();
            $table->timestamps();
            $table->index(['source_month_folder', 'source_sheet']);
            $table->index('migration_batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staging_libro_banco');
    }
};
