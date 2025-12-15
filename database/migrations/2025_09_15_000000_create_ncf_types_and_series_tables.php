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
        Schema::create('ncf_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('ncf_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ncf_type_id')->constrained('ncf_types')->cascadeOnDelete();
            $table->string('name');
            $table->string('prefix', 20)->nullable();
            $table->unsignedBigInteger('start_number');
            $table->unsignedBigInteger('end_number');
            $table->unsignedBigInteger('current_number')->nullable();
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->enum('status', ['active', 'expired', 'depleted'])->default('active');
            $table->timestamps();

            $table->unique(['ncf_type_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ncf_series');
        Schema::dropIfExists('ncf_types');
    }
};
