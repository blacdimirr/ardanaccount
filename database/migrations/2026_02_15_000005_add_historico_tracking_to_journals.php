<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_entries', 'migration_batch_id')) {
                $table->unsignedBigInteger('migration_batch_id')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn('journal_entries', 'source_type')) {
                $table->string('source_type')->nullable()->after('migration_batch_id');
            }
            if (!Schema::hasColumn('journal_entries', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }
            if (!Schema::hasColumn('journal_entries', 'source_hash')) {
                $table->string('source_hash', 40)->nullable()->after('source_id');
            }
        });

        Schema::table('journal_items', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_items', 'migration_batch_id')) {
                $table->unsignedBigInteger('migration_batch_id')->nullable()->after('credit');
            }
            if (!Schema::hasColumn('journal_items', 'source_type')) {
                $table->string('source_type')->nullable()->after('migration_batch_id');
            }
            if (!Schema::hasColumn('journal_items', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }
            if (!Schema::hasColumn('journal_items', 'source_hash')) {
                $table->string('source_hash', 40)->nullable()->after('source_id');
            }
        });

        Schema::table('transaction_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('transaction_lines', 'migration_batch_id')) {
                $table->unsignedBigInteger('migration_batch_id')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn('transaction_lines', 'source_type')) {
                $table->string('source_type')->nullable()->after('migration_batch_id');
            }
            if (!Schema::hasColumn('transaction_lines', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }
            if (!Schema::hasColumn('transaction_lines', 'source_hash')) {
                $table->string('source_hash', 40)->nullable()->after('source_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropColumn(['migration_batch_id', 'source_type', 'source_id', 'source_hash']);
        });

        Schema::table('journal_items', function (Blueprint $table) {
            $table->dropColumn(['migration_batch_id', 'source_type', 'source_id', 'source_hash']);
        });

        Schema::table('transaction_lines', function (Blueprint $table) {
            $table->dropColumn(['migration_batch_id', 'source_type', 'source_id', 'source_hash']);
        });
    }
};
