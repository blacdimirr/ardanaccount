<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_bancarios', function (Blueprint $table) {
            if (!Schema::hasColumn('movimientos_bancarios', 'migration_batch_id')) {
                $table->unsignedBigInteger('migration_batch_id')->nullable()->after('conciliable_type');
            }
            if (!Schema::hasColumn('movimientos_bancarios', 'staging_id')) {
                $table->unsignedBigInteger('staging_id')->nullable()->after('migration_batch_id');
            }
            if (!Schema::hasColumn('movimientos_bancarios', 'source_hash')) {
                $table->string('source_hash', 40)->nullable()->after('staging_id');
                $table->unique('source_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_bancarios', function (Blueprint $table) {
            if (Schema::hasColumn('movimientos_bancarios', 'source_hash')) {
                $table->dropUnique(['source_hash']);
            }
            $columns = array_filter(['migration_batch_id', 'staging_id', 'source_hash'], function ($column) {
                return Schema::hasColumn('movimientos_bancarios', $column);
            });
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
