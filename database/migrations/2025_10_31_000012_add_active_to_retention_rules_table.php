<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActiveToRetentionRulesTable extends Migration
{
    public function up(): void
    {
        Schema::table('retention_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('retention_rules', 'active')) {
                $table->boolean('active')->default(true)->after('isr_retention_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('retention_rules', function (Blueprint $table) {
            if (Schema::hasColumn('retention_rules', 'active')) {
                $table->dropColumn('active');
            }
        });
    }
}
