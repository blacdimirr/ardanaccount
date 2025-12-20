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
        'created_by',
    ];

    public function serviceCategory()
    {
        return $this->belongsTo(ProductServiceCategory::class, 'service_category_id');
    }
}
