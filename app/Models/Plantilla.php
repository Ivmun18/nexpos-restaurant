<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model
{
    protected $table = 'plantillas';

    protected $fillable = [
        'industry_type',
        'codigo',
        'nombre',
        'descripcion',
        'total_productos',
        'total_categorias',
        'seeder_class',
        'activa',
        'orden',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function scopeActivas($query)
    {
        return $query->where('activa', true)->orderBy('orden');
    }

    public function scopePorIndustria($query, $industry)
    {
        return $query->where('industry_type', $industry);
    }
}
