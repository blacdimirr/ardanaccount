<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FondoRotatorio extends Model
{
    protected $table = 'fondos_rotatorios';

    protected $fillable = [
        'nombre',
        'monto_inicial',
        'monto_disponible',
        'cuenta_contable_id',
        'created_by',
    ];

    public function cuentaContable()
    {
        return $this->hasOne(ChartOfAccount::class, 'id', 'cuenta_contable_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoFondo::class, 'fondo_id');
    }
}
