<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuentaRecaudadora extends Model
{
    protected $table = 'cuentas_recaudadoras';

    protected $fillable = [
        'banco',
        'numero_cuenta',
        'tipo',
        'activo',
        'created_by',
    ];

    public function recaudaciones()
    {
        return $this->hasMany(Recaudacion::class, 'cuenta_recaudadora_id');
    }

    public function movimientosBancarios()
    {
        return $this->hasMany(MovimientoBancario::class, 'cuenta_recaudadora_id');
    }
}
