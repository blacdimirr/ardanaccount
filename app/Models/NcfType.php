<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NcfType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
    ];

    public function series()
    {
        return $this->hasMany(NcfSeries::class);
    }
}
