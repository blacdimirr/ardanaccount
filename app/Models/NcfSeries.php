<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NcfSeries extends Model
{
    protected $table = 'ncf_series';

    protected $fillable = [
        'ncf_type_id',
        'series',
        'start_number',
        'end_number',
        'current_number',
        'valid_from',
        'valid_to',
        'status',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function type()
    {
        return $this->belongsTo(NcfType::class, 'ncf_type_id');
    }
}
