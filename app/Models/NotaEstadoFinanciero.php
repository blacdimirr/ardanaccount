<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaEstadoFinanciero extends Model
{
    protected $table = 'notas_estados_financieros';

    protected $fillable = [
        'codigo_nota',
        'titulo',
        'contenido',
        'periodo',
        'estado',
        'created_by',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'periodo' => 'date',
    ];
}
