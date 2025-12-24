<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigIsrTramo extends Model
{
    protected $table = 'config_isr_tramos';

    protected $fillable = [
        'rango_desde',
        'rango_hasta',
        'tasa',
        'created_by',
    ];

    protected $casts = [
        'rango_desde' => 'float',
        'rango_hasta' => 'float',
        'tasa' => 'float',
    ];
}
