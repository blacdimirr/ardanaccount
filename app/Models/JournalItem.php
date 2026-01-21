<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalItem extends Model
{
    protected $fillable = [
        'journal',
        'account',
        'servicio_id',
        'description',
        'debit',
        'credit',
        'migration_batch_id',
        'source_type',
        'source_id',
        'source_hash',
    ];

    public function accounts()
    {
        return $this->hasOne('App\Models\ChartOfAccount', 'id', 'account');
    }

    public function servicioUnidad()
    {
        return $this->belongsTo(ServicioUnidad::class, 'servicio_id');
    }


}
