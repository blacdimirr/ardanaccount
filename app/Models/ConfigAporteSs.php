<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigAporteSs extends Model
{
    protected $table = 'config_aportes_ss';

    protected $fillable = [
        'tss_empleador',
        'tss_empleado',
        'infotep_empleador',
        'infotep_empleado',
        'idoppril_empleador',
        'idoppril_empleado',
        'created_by',
    ];
}
