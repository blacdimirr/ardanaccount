<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'sub_type',
        'is_enabled',
        'description',
        'created_by',
        'objeto_gasto_id',
        'fuente_financiamiento_id',
        'programa_id',
        'proyecto_id',
    ];

    public function types()
    {
        return $this->hasOne('App\Models\ChartOfAccountType', 'id', 'type');
    }

    public function accounts()
    {
        return $this->hasOne('App\Models\JournalItem', 'account', 'id');
    }

    public function balance()
    {
        $journalItem         = JournalItem::select(\DB::raw('sum(credit) as totalCredit'), \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) - sum(debit) as netAmount'))->where('account', $this->id);
        $journalItem         = $journalItem->first();
        $data['totalCredit'] = $journalItem->totalCredit;
        $data['totalDebit']  = $journalItem->totalDebit;
        $data['netAmount']   = $journalItem->netAmount;

        return $data;
    }

    public function subType()
    {
        return $this->hasOne('App\Models\ChartOfAccountSubType', 'id', 'sub_type');
    }

    public function parentAccount()
    {
        return $this->hasOne('App\Models\ChartOfAccountParent', 'id', 'parent');
    }

    public function bankAccount()
    {
        return $this->hasOne('App\Models\BankAccount', 'chart_account_id', 'id');
    }

    public function objetoGasto()
    {
        return $this->belongsTo(ClasificadorObjetoGasto::class, 'objeto_gasto_id');
    }

    public function fuenteFinanciamiento()
    {
        return $this->belongsTo(FundingSource::class, 'fuente_financiamiento_id');
    }

    public function programa()
    {
        return $this->belongsTo(Program::class, 'programa_id');
    }

    public function proyecto()
    {
        return $this->belongsTo(Project::class, 'proyecto_id');
    }
}
