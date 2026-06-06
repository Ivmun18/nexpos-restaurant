<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotariaServicio extends Model
{
    protected $table = 'notaria_servicios';
    protected $fillable = ['empresa_id', 'nombre', 'precio', 'activo'];
    protected $casts = ['precio' => 'float', 'activo' => 'boolean'];
}
