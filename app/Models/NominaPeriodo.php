<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominaPeriodo extends Model
{
    protected $table = 'nomina_periodos';

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'journal_entry_id',
        'created_by',
    ];

    public static $estados = [
        'abierto' => 'Abierto',
        'cerrado' => 'Cerrado',
    ];

    public function detallesNomina()
    {
        return $this->hasMany(NominaDetalle::class, 'nomina_periodo_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }
}
