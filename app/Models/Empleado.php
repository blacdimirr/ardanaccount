<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'documento_identidad',
        'email',
        'telefono',
        'direccion',
        'tipo_vinculo',
        'tipo_contribuyente',
        'unidad_servicio',
        'servicio_id',
        'salario',
        'created_by',
    ];

    public function detallesNomina()
    {
        return $this->hasMany(NominaDetalle::class, 'empleado_id');
    }

    public function servicioUnidad()
    {
        return $this->belongsTo(ServicioUnidad::class, 'servicio_id');
    }

    public function getNombreCompletoAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
