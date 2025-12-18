<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillProduct extends Model
{
    protected $table = 'bill_products'; // si tu tabla se llama así
    protected $fillable = [
        'product_id', 'bill_id', 'quantity', 'tax', 'discount', 'total',
    ];

    protected $casts = [
        'quantity' => 'float',
        'tax'      => 'float',
        'discount' => 'float',
        'total'    => 'float',
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
