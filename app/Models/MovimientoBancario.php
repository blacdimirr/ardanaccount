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
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function cuentaRecaudadora()
    {
        return $this->belongsTo(CuentaRecaudadora::class, 'cuenta_recaudadora_id');
    }
}
