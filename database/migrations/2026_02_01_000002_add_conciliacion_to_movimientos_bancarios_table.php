<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConciliacionToMovimientosBancariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movimientos_bancarios', function (Blueprint $table) {
            $table->string('estado_conciliacion')->default('pendiente')->after('origen_archivo');
            $table->unsignedBigInteger('conciliable_id')->nullable()->after('estado_conciliacion');
            $table->string('conciliable_type')->nullable()->after('conciliable_id');
            $table->index(['conciliable_type', 'conciliable_id'], 'movimientos_conciliable_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movimientos_bancarios', function (Blueprint $table) {
            $table->dropIndex('movimientos_conciliable_index');
            $table->dropColumn(['estado_conciliacion', 'conciliable_id', 'conciliable_type']);
        });
    }
}
