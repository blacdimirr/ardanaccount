<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetentionCertificate extends Model
{
    protected $fillable = [
        'bill_id',
        'vender_id',
        'supplier_type',
        'itbis_amount',
        'isr_amount',
        'issued_at',
        'created_by',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function vender()
    {
        return $this->belongsTo(Vender::class, 'vender_id');
    }
}
