<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiciosToNomina extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicios_unidades', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre');
            $table->integer('created_by')->default(0);
            $table->timestamps();
            $table->unique(['nombre', 'created_by']);
        });

        Schema::table('empleados', function (Blueprint $table) {
            $table->unsignedBigInteger('servicio_id')->nullable()->after('unidad_servicio');
            $table->index('servicio_id');
        });

        Schema::table('nomina_detalles', function (Blueprint $table) {
            $table->unsignedBigInteger('servicio_id')->nullable()->after('nomina_concepto_id');
            $table->index('servicio_id');
        });

        Schema::table('journal_items', function (Blueprint $table) {
            $table->unsignedBigInteger('servicio_id')->nullable()->after('account');
            $table->index('servicio_id');
        });

        Schema::table('nomina_periodos', function (Blueprint $table) {
            $table->unsignedBigInteger('journal_entry_id')->nullable()->after('estado');
            $table->index('journal_entry_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nomina_periodos', function (Blueprint $table) {
            $table->dropIndex(['journal_entry_id']);
            $table->dropColumn('journal_entry_id');
        });

        Schema::table('journal_items', function (Blueprint $table) {
            $table->dropIndex(['servicio_id']);
            $table->dropColumn('servicio_id');
        });

        Schema::table('nomina_detalles', function (Blueprint $table) {
            $table->dropIndex(['servicio_id']);
            $table->dropColumn('servicio_id');
        });

        Schema::table('empleados', function (Blueprint $table) {
            $table->dropIndex(['servicio_id']);
            $table->dropColumn('servicio_id');
        });

        Schema::dropIfExists('servicios_unidades');
    }
}
