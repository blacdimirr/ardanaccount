<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicEquityVariationMappingsTable extends Migration
{
    public function up()
    {
        Schema::create('public_equity_variation_mappings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('line_name');
            $table->string('section');
            $table->unsignedBigInteger('chart_of_account_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->integer('created_by')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('public_equity_variation_mappings');
    }
}
