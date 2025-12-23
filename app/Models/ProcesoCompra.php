<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcesoCompra extends Model
{
    protected $table = 'procesos_compra';

    protected $fillable = [
        'requisicion_id',
        'descripcion',
        'fecha_inicio',
        'estado',
        'created_by',
    ];

    public function requisicion()
    {
        return $this->belongsTo(Requisicion::class, 'requisicion_id');
    }

    public function ofertas()
    {
        return $this->hasMany(Oferta::class, 'proceso_compra_id');
    }

    public function adjudicacion()
    {
        return $this->hasOne(Adjudicacion::class, 'proceso_compra_id');
    }

    public static function estados(): array
    {
        return [
            'en_proceso' => __('In process'),
            'adjudicado' => __('Awarded'),
        ];
    }
}
