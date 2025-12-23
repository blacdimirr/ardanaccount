<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActiveFlagsToNcfTables extends Migration
{
    public function up(): void
    {
        Schema::table('ncf_types', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('description');
        });

        Schema::table('ncf_series', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('ncf_series', function (Blueprint $table) {
            $table->dropColumn('active');
        });

        Schema::table('ncf_types', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
}
