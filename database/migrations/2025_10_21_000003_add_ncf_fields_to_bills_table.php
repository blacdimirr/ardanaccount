<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNcfFieldsToBillsTable extends Migration
{
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->unsignedBigInteger('ncf_type_id')->nullable()->after('order_number');
            $table->unsignedBigInteger('ncf_series_id')->nullable()->after('ncf_type_id');
            $table->text('ncf_number')->nullable()->after('ncf_series_id');

            $table->foreign('ncf_type_id')->references('id')->on('ncf_types')->nullOnDelete();
            $table->foreign('ncf_series_id')->references('id')->on('ncf_series')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['ncf_type_id']);
            $table->dropForeign(['ncf_series_id']);
            $table->dropColumn(['ncf_type_id', 'ncf_series_id', 'ncf_number']);
        });
    }
}
