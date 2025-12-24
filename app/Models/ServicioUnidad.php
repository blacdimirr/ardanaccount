<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicioUnidad extends Model
{
    protected $table = 'servicios_unidades';

    protected $fillable = [
        'nombre',
        'created_by',
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'servicio_id');
    }
}
