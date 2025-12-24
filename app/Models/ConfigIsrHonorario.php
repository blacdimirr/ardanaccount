<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigIsrHonorario extends Model
{
    protected $table = 'config_isr_honorarios';

    protected $fillable = [
        'retencion_honorarios',
        'created_by',
    ];

    protected $casts = [
        'retencion_honorarios' => 'float',
    ];
}
