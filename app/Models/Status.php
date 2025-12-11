<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $fillable = ['name'];

    /**
     * Devuelve el nombre del estatus dado su ID.
     *
     * @param int $id
     * @return string|null
     */
    public static function getNameById($id)
    {
        return self::where('id', $id)->value('name');
    }

    public static function getAllAsArray(): array
    {
        return self::pluck('name', 'id')->toArray();
    }
}
