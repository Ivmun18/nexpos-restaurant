<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitucionMinimarket extends Model
{
    protected $table = 'instituciones_minimarket';

    protected $fillable = [
        'empresa_id', 'nombre', 'porcentaje_recargo', 'activo',
    ];
}
