<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGovernmentRetentionToRulesAndBills extends Migration
{
    public function up(): void
    {
        Schema::table('retention_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('retention_rules', 'government_retention_rate')) {
                $table->decimal('government_retention_rate', 5, 2)->default(0)->after('isr_retention_rate');
            }
        });

        Schema::table('bills', function (Blueprint $table) {
            if (!Schema::hasColumn('bills', 'government_withheld_total')) {
                $table->decimal('government_withheld_total', 15, 2)->default(0)->after('isr_withheld_total');
            }
        });

        Schema::table('bill_products', function (Blueprint $table) {
            if (!Schema::hasColumn('bill_products', 'government_withheld_amount')) {
                $table->decimal('government_withheld_amount', 15, 2)->default(0)->after('isr_withheld_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bill_products', function (Blueprint $table) {
            if (Schema::hasColumn('bill_products', 'government_withheld_amount')) {
                $table->dropColumn('government_withheld_amount');
            }
        });

        Schema::table('bills', function (Blueprint $table) {
            if (Schema::hasColumn('bills', 'government_withheld_total')) {
                $table->dropColumn('government_withheld_total');
            }
        });

        Schema::table('retention_rules', function (Blueprint $table) {
            if (Schema::hasColumn('retention_rules', 'government_retention_rate')) {
                $table->dropColumn('government_retention_rate');
            }
        });
    }
}
