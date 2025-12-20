<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierTypeToVendersTable extends Migration
{
    public function up(): void
    {
        Schema::table('venders', function (Blueprint $table) {
            $table->string('supplier_type', 100)->nullable()->after('tax_number');
        });
    }

    public function down(): void
    {
        Schema::table('venders', function (Blueprint $table) {
            $table->dropColumn('supplier_type');
        });
    }
}
