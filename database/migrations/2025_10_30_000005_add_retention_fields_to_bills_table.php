<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRetentionFieldsToBillsTable extends Migration
{
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->string('has_retention', 100)->nullable()->after('vender_id');
            $table->string('supplier_type', 100)->nullable()->after('has_retention');
            $table->decimal('itbis_billed_total', 15, 2)->default(0)->after('ncf_number');
            $table->decimal('itbis_withheld_total', 15, 2)->default(0)->after('itbis_billed_total');
            $table->decimal('isr_withheld_total', 15, 2)->default(0)->after('itbis_withheld_total');
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['supplier_type', 'itbis_billed_total', 'itbis_withheld_total', 'isr_withheld_total']);
        });
    }
}
