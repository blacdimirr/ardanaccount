<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    protected $fillable = [
        'proceso_compra_id',
        'proveedor',
        'monto_ofertado',
        'fecha_oferta',
        'estado',
        'created_by',
    ];

    public function procesoCompra()
    {
        return $this->belongsTo(ProcesoCompra::class, 'proceso_compra_id');
    }

    public static function estados(): array
    {
        return [
            'presentada' => __('Submitted'),
            'evaluada' => __('Evaluated'),
        ];
    }
}
