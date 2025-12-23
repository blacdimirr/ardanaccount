<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NcfType extends Model
{
    protected $fillable = [
        'code',
        'description',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function series()
    {
        return $this->hasMany(NcfSeries::class, 'ncf_type_id');
    }
}
