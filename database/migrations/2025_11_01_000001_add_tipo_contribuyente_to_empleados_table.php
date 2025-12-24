<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTipoContribuyenteToEmpleadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->string('tipo_contribuyente')->default('asalariado')->after('tipo_vinculo');
        });

        DB::table('empleados')
            ->where('tipo_vinculo', 'honorarios')
            ->update(['tipo_contribuyente' => 'honorarios']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn('tipo_contribuyente');
        });
    }
}
