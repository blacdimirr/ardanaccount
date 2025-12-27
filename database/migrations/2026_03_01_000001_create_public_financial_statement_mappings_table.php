<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicFinancialStatementMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('public_financial_statement_mappings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('line_name');
            $table->string('section');
            $table->unsignedBigInteger('chart_of_account_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->integer('created_by')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('public_financial_statement_mappings');
    }
}
