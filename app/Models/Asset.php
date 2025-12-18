<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'name',
        'purchase_date',
        'supported_date',
        'amount',
        'description',

        //new fields
        'area',
        'code_active',
        'code_active_category',
        'date_garantia',


        //new fields 2
        'proveedor_id',
        
        'created_by',
    ];

}
