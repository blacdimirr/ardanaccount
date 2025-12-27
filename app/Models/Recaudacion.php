<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recaudacion extends Model
{
    protected $table = 'recaudaciones';

    protected $fillable = [
        'fecha',
        'servicio',
        'monto',
        'metodo_pago',
        'cuenta_recaudadora_id',
        'paciente_id',
        'created_by',
        'estado_conciliacion',
    ];

    public function cuentaRecaudadora()
    {
        return $this->belongsTo(CuentaRecaudadora::class, 'cuenta_recaudadora_id');
    }

    public function movimientoBancario()
    {
        return $this->morphOne(MovimientoBancario::class, 'conciliable');
    }
}
