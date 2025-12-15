<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('ncf_type_id')->nullable()->after('ref_number');
            $table->unsignedBigInteger('ncf_series_id')->nullable()->after('ncf_type_id');
            $table->string('ncf_number')->nullable()->after('ncf_series_id');

            $table->foreign('ncf_type_id')->references('id')->on('ncf_types')->onDelete('set null');
            $table->foreign('ncf_series_id')->references('id')->on('ncf_series')->onDelete('set null');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('ncf_type_id')->nullable()->after('description');
            $table->string('ncf_series')->nullable()->after('ncf_type_id');
            $table->string('ncf_number')->nullable()->after('ncf_series');

            $table->foreign('ncf_type_id')->references('id')->on('ncf_types')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['ncf_type_id']);
            $table->dropForeign(['ncf_series_id']);
            $table->dropColumn(['ncf_type_id', 'ncf_series_id', 'ncf_number']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['ncf_type_id']);
            $table->dropColumn(['ncf_type_id', 'ncf_series', 'ncf_number']);
        });
    }
};
