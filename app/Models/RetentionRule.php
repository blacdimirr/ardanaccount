<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetentionRule extends Model
{
    protected $fillable = [
        'supplier_type',
        'service_category_id',
        'itbis_retention_rate',
        'isr_retention_rate',
        'active',
        'created_by',
    ];

    protected $casts = [
        'itbis_retention_rate' => 'float',
        'isr_retention_rate' => 'float',
        'active' => 'boolean',
    ];

    public function serviceCategory()
    {
        return $this->belongsTo(ProductServiceCategory::class, 'service_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
