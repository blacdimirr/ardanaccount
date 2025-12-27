<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicFinancialStatementMapping extends Model
{
    protected $fillable = [
        'line_name',
        'section',
        'chart_of_account_id',
        'sort_order',
        'created_by',
    ];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
