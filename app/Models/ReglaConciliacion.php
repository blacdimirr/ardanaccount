<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReglaConciliacion extends Model
{
    protected $table = 'reglas_conciliacion';

    protected $fillable = [
        'nombre',
        'descripcion',
        'usar_referencia',
        'usar_monto',
        'usar_fecha',
        'tolerancia_monto',
        'rango_dias',
        'activo',
        'created_by',
    ];

    protected $casts = [
        'usar_referencia' => 'boolean',
        'usar_monto' => 'boolean',
        'usar_fecha' => 'boolean',
        'activo' => 'boolean',
        'tolerancia_monto' => 'decimal:2',
    ];
}
