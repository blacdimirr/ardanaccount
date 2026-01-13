<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('migration_batches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('month_folder');
            $table->string('source_file')->nullable();
            $table->string('status')->default('RUNNING');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->json('totals_json')->nullable();
            $table->timestamps();
            $table->index('month_folder');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('migration_batches');
    }
};
