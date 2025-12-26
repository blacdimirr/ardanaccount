<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominaConcepto extends Model
{
    protected $table = 'nomina_conceptos';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'naturaleza',
        'monto',
        'aplica_isr',
        'aplica_tss',
        'nomina_periodo_id',
        'created_by',
    ];

    public static $tipos = [
        'ingreso' => 'Ingreso',
        'descuento' => 'Descuento',
        'aporte' => 'Aporte',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'aplica_isr' => 'boolean',
        'aplica_tss' => 'boolean',
    ];

    public function periodo()
    {
        return $this->belongsTo(NominaPeriodo::class, 'nomina_periodo_id');
    }

    public function detallesNomina()
    {
        return $this->hasMany(NominaDetalle::class, 'nomina_concepto_id');
    }
}
