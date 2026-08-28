<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComandaLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'mesa_id',
        'mesa_nombre',
        'mozo_id',
        'mozo_nombre',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];
}
