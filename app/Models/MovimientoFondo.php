<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoFondo extends Model
{
    protected $table = 'movimientos_fondo';

    protected $fillable = [
        'fondo_id',
        'tipo',
        'monto',
        'fecha',
        'descripcion',
        'comprobante_id',
        'created_by',
    ];

    public function fondo()
    {
        return $this->belongsTo(FondoRotatorio::class, 'fondo_id');
    }
}
