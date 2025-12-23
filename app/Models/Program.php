<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $table = 'programas';

    protected $fillable = [
        'code',
        'name',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class, 'program_id');
    }
}
