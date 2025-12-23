<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->json('monto_pia')->nullable()->after('expense_data');
            $table->json('monto_pim')->nullable()->after('monto_pia');
        });

        Schema::create('budget_pim_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_id');
            $table->unsignedBigInteger('category_id');
            $table->decimal('monto_anterior', 15, 2)->default(0);
            $table->decimal('monto_nuevo', 15, 2)->default(0);
            $table->date('fecha')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('budget_id')->references('id')->on('budgets')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('product_service_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn('monto_pia');
            $table->dropColumn('monto_pim');
        });

        Schema::dropIfExists('budget_pim_histories');
    }
};
