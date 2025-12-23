<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductServiceCategory;

class ContratoCompra extends Model
{
    protected $table = 'contratos';

    protected $fillable = [
        'adjudicacion_id',
        'partida_presupuestaria_id',
        'proveedor',
        'monto_contrato',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'compromiso_aplicado',
        'created_by',
    ];

    public function adjudicacion()
    {
        return $this->belongsTo(Adjudicacion::class, 'adjudicacion_id');
    }

    public function partidaPresupuestaria()
    {
        return $this->belongsTo(ProductServiceCategory::class, 'partida_presupuestaria_id');
    }

    public static function estados(): array
    {
        return [
            'vigente' => __('Active'),
            'cerrado' => __('Closed'),
        ];
    }
}
