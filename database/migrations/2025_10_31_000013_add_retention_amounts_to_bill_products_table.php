<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRetentionAmountsToBillProductsTable extends Migration
{
    public function up(): void
    {
        Schema::table('bill_products', function (Blueprint $table) {
            if (!Schema::hasColumn('bill_products', 'itbis_amount')) {
                $table->decimal('itbis_amount', 15, 2)->default(0)->after('price');
            }

            if (!Schema::hasColumn('bill_products', 'itbis_withheld_amount')) {
                $table->decimal('itbis_withheld_amount', 15, 2)->default(0)->after('itbis_amount');
            }

            if (!Schema::hasColumn('bill_products', 'isr_withheld_amount')) {
                $table->decimal('isr_withheld_amount', 15, 2)->default(0)->after('itbis_withheld_amount');
            }

            if (!Schema::hasColumn('bill_products', 'retention_rule_id')) {
                $table->unsignedBigInteger('retention_rule_id')->nullable()->after('category_id');
                $table->foreign('retention_rule_id')->references('id')->on('retention_rules')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bill_products', function (Blueprint $table) {
            if (Schema::hasColumn('bill_products', 'retention_rule_id')) {
                $table->dropForeign(['retention_rule_id']);
                $table->dropColumn('retention_rule_id');
            }

            foreach (['itbis_amount', 'itbis_withheld_amount', 'isr_withheld_amount'] as $column) {
                if (Schema::hasColumn('bill_products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
