<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominaPeriodo extends Model
{
    protected $table = 'nomina_periodos';

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'created_by',
    ];

    public static $estados = [
        'abierto' => 'Abierto',
        'cerrado' => 'Cerrado',
    ];

    public function detallesNomina()
    {
        return $this->hasMany(NominaDetalle::class, 'nomina_periodo_id');
    }
}
