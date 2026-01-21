<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'migration_batch_id')) {
                $table->unsignedBigInteger('migration_batch_id')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn('payments', 'staging_id')) {
                $table->unsignedBigInteger('staging_id')->nullable()->after('migration_batch_id');
            }
            if (!Schema::hasColumn('payments', 'source_hash')) {
                $table->string('source_hash', 40)->nullable()->after('staging_id');
            }
        });

        Schema::table('bills', function (Blueprint $table) {
            if (!Schema::hasColumn('bills', 'migration_batch_id')) {
                $table->unsignedBigInteger('migration_batch_id')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn('bills', 'staging_id')) {
                $table->unsignedBigInteger('staging_id')->nullable()->after('migration_batch_id');
            }
            if (!Schema::hasColumn('bills', 'source_hash')) {
                $table->string('source_hash', 40)->nullable()->after('staging_id');
            }
        });

        Schema::table('revenues', function (Blueprint $table) {
            if (!Schema::hasColumn('revenues', 'migration_batch_id')) {
                $table->unsignedBigInteger('migration_batch_id')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn('revenues', 'staging_id')) {
                $table->unsignedBigInteger('staging_id')->nullable()->after('migration_batch_id');
            }
            if (!Schema::hasColumn('revenues', 'source_hash')) {
                $table->string('source_hash', 40)->nullable()->after('staging_id');
            }
        });

        Schema::table('bill_products', function (Blueprint $table) {
            if (!Schema::hasColumn('bill_products', 'migration_batch_id')) {
                $table->unsignedBigInteger('migration_batch_id')->nullable()->after('description');
            }
            if (!Schema::hasColumn('bill_products', 'staging_id')) {
                $table->unsignedBigInteger('staging_id')->nullable()->after('migration_batch_id');
            }
            if (!Schema::hasColumn('bill_products', 'source_hash')) {
                $table->string('source_hash', 40)->nullable()->after('staging_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['migration_batch_id', 'staging_id', 'source_hash']);
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['migration_batch_id', 'staging_id', 'source_hash']);
        });

        Schema::table('revenues', function (Blueprint $table) {
            $table->dropColumn(['migration_batch_id', 'staging_id', 'source_hash']);
        });

        Schema::table('bill_products', function (Blueprint $table) {
            $table->dropColumn(['migration_batch_id', 'staging_id', 'source_hash']);
        });
    }
};
