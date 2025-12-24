<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominaDetalle extends Model
{
    protected $table = 'nomina_detalles';

    protected $fillable = [
        'nomina_periodo_id',
        'empleado_id',
        'nomina_concepto_id',
        'monto',
        'created_by',
    ];

    public function periodo()
    {
        return $this->belongsTo(NominaPeriodo::class, 'nomina_periodo_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function concepto()
    {
        return $this->belongsTo(NominaConcepto::class, 'nomina_concepto_id');
    }
}
