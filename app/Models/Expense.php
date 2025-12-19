<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\NcfSeries;
use App\Models\NcfType;

class Expense extends Model
{
    protected $fillable = [
        'category_id','description','amount','date','project_id','user_id','attachment','created_by','ncf_type_id','ncf_series_id','ncf_number'
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

    public function ncfSeries()
    {
        return $this->belongsTo(NcfSeries::class, 'ncf_series_id');
    }
}
