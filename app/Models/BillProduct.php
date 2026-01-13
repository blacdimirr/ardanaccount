<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillProduct extends Model
{
    protected $table = 'bill_products'; // si tu tabla se llama así
    protected $fillable = [
        'product_id',
        'bill_id',
        'quantity',
        'tax',
        'discount',
        'total',
        'price',
        'description',
        'category_id',
        'itbis_amount',
        'itbis_withheld_amount',
        'isr_withheld_amount',
        'government_withheld_amount',
        'retention_rule_id',
        'migration_batch_id',
        'staging_id',
        'source_hash',
    ];

    protected $casts = [
        'quantity' => 'float',
        'tax'      => 'float',
        'discount' => 'float',
        'total'    => 'float',
        'price'    => 'float',
        'itbis_amount' => 'float',
        'itbis_withheld_amount' => 'float',
        'isr_withheld_amount' => 'float',
        'government_withheld_amount' => 'float',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductService::class, 'product_id');
    }
}
