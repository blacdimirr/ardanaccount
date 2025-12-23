<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetPimHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id',
        'category_id',
        'monto_anterior',
        'monto_nuevo',
        'fecha',
        'created_by',
        'reason',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductServiceCategory::class, 'category_id');
    }
}
