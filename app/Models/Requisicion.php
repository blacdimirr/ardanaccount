<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisicion extends Model
{
    protected $fillable = [
        'area_solicitante',
        'descripcion',
        'fecha_requisicion',
        'estado',
        'created_by',
    ];

    public function procesosCompra()
    {
        return $this->hasMany(ProcesoCompra::class, 'requisicion_id');
    }

    public static function estados(): array
    {
        return [
            'borrador' => __('Draft'),
            'en_proceso' => __('In process'),
            'aprobado' => __('Approved'),
        ];
    }
}
