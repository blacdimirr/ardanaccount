<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'period',
        'created_by',
        'monto_pia',
        'monto_pim',
    ];

    protected $casts = [
        'monto_pia' => 'array',
        'monto_pim' => 'array',
    ];

    public static $period = [
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'half-yearly' =>'Half Yearly',
        'yearly' => 'Yearly',

    ];

    public function getAvailabilityDate()
    {

        $start_date = '';
        $end_date = '';
        $date = '';
        $date_formate =('M-Y');
        if(!empty($this->start_date)) {
            $start_date = date ($date_formate , strtotime($this->start_date) );
            $date = $start_date;
        }
        if(!empty($this->end_date)) {
            $end_date = date ($date_formate , strtotime($this->end_date) );
            $date .= ' - ' .$end_date.' ';
        }


        return $date;
    }

    public static function percentage($actual,$budget)
    {
        $percentage = $budget*100/$actual;
        return  number_format($percentage,2);

    }
}
