<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductServiceCategory;

class Adjudicacion extends Model
{
    protected $fillable = [
        'proceso_compra_id',
        'oferta_id',
        'partida_presupuestaria_id',
        'monto_adjudicado',
        'fecha_adjudicacion',
        'estado',
        'compromiso_aplicado',
        'created_by',
    ];

    public function procesoCompra()
    {
        return $this->belongsTo(ProcesoCompra::class, 'proceso_compra_id');
    }

    public function oferta()
    {
        return $this->belongsTo(Oferta::class, 'oferta_id');
    }

    public function partidaPresupuestaria()
    {
        return $this->belongsTo(ProductServiceCategory::class, 'partida_presupuestaria_id');
    }

    public function contrato()
    {
        return $this->hasOne(ContratoCompra::class, 'adjudicacion_id');
    }

    public static function estados(): array
    {
        return [
            'aprobado' => __('Approved'),
            'contratado' => __('Contracted'),
        ];
    }
}
