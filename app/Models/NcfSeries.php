<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NcfSeries extends Model
{
    use HasFactory;

    protected $fillable = [
        'ncf_type_id',
        'name',
        'prefix',
        'start_number',
        'end_number',
        'current_number',
        'valid_from',
        'valid_until',
        'status',
    ];

    public function type()
    {
        return $this->belongsTo(NcfType::class, 'ncf_type_id');
    }
}
