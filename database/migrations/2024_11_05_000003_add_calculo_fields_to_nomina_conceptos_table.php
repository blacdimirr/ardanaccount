<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCalculoFieldsToNominaConceptosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nomina_conceptos', function (Blueprint $table) {
            $table->decimal('monto', 15, 2)->default(0)->after('naturaleza');
            $table->boolean('aplica_isr')->default(false)->after('monto');
            $table->boolean('aplica_tss')->default(false)->after('aplica_isr');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nomina_conceptos', function (Blueprint $table) {
            $table->dropColumn(['monto', 'aplica_isr', 'aplica_tss']);
        });
    }
}
