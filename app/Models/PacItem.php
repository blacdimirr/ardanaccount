<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PacItem extends Model
{
    protected $fillable = [
        'pac_id',
        'descripcion',
        'partida_presupuestaria_id',
        'objeto_gasto_id',
        'fuente_financiamiento_id',
        'tipo_procedimiento',
        'monto_estimado',
    ];

    protected $casts = [
        'monto_estimado' => 'decimal:2',
    ];

    public function pac()
    {
        return $this->belongsTo(Pac::class);
    }

    public function partidaPresupuestaria()
    {
        return $this->belongsTo(ProductServiceCategory::class, 'partida_presupuestaria_id');
    }

    public function objetoGasto()
    {
        return $this->belongsTo(ClasificadorObjetoGasto::class, 'objeto_gasto_id');
    }

    public function fuenteFinanciamiento()
    {
        return $this->belongsTo(FundingSource::class, 'fuente_financiamiento_id');
    }
}
