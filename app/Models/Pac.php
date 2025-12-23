<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pac extends Model
{
    protected $table = 'pac';

    protected $fillable = [
        'anio',
        'descripcion',
        'created_by',
    ];

    public function items()
    {
        return $this->hasMany(PacItem::class);
    }
}
