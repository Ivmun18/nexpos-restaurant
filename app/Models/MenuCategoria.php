<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuCategoria extends Model
{
    protected $table = 'menu_categorias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'icono',
        'color',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden'  => 'integer',
    ];

    public function productos(): HasMany
    {
        return $this->hasMany(MenuProducto::class, 'menu_categoria_id')
                    ->orderBy('orden');
    }

    public function productosActivos(): HasMany
    {
        return $this->hasMany(MenuProducto::class, 'menu_categoria_id')
                    ->where('activo', true)
                    ->orderBy('orden');
    }

    // Scope para categorías activas ordenadas
    public function scopeActivas($query)
    {
        return $query->where('activo', true)->orderBy('orden');
    }
}