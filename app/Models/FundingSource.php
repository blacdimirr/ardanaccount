<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundingSource extends Model
{
    protected $table = 'fuentes_financiamiento';

    protected $fillable = [
        'code',
        'description',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
