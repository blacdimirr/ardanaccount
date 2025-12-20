<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierType extends Model
{
    protected $fillable = [
        'name',
        'created_by',
    ];

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($query) use ($userId) {
            $query->where('created_by', $userId)
                ->orWhere('created_by', 0);
        });
    }
}
