<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClasificadorObjetoGasto extends Model
{
    protected $table = 'clasificador_objeto_gasto';

    protected $fillable = [
        'code',
        'description',
        'level',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
