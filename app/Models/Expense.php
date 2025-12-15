<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'category_id','description','ncf_type_id','ncf_series','ncf_number','amount','date','project_id','user_id','attachment','created_by'
    ];

    public function category(){
        return $this->hasOne('App\Models\ExpensesCategory','id','category_id');
    }
    public function projects(){
        return $this->hasOne('App\Models\Projects','id','project');
    }
    public function user(){
        return $this->hasOne('App\Models\User','id','user_id');
    }

    public function ncfType()
    {
        return $this->belongsTo(NcfType::class, 'ncf_type_id');
    }
}
