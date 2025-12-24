<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominaConcepto extends Model
{
    protected $table = 'nomina_conceptos';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'naturaleza',
        'created_by',
    ];

    public static $tipos = [
        'ingreso' => 'Ingreso',
        'descuento' => 'Descuento',
    ];

    public function detallesNomina()
    {
        return $this->hasMany(NominaDetalle::class, 'nomina_concepto_id');
    }
}
