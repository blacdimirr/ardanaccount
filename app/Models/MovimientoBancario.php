<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoBancario extends Model
{
    protected $table = 'movimientos_bancarios';

    protected $fillable = [
        'cuenta_recaudadora_id',
        'fecha',
        'monto',
        'descripcion',
        'referencia',
        'origen_archivo',
        'estado_conciliacion',
        'conciliable_id',
        'conciliable_type',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function cuentaRecaudadora()
    {
        return $this->belongsTo(CuentaRecaudadora::class, 'cuenta_recaudadora_id');
    }

    public function conciliable()
    {
        return $this->morphTo();
    }
}
