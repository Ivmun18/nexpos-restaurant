<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuProducto extends Model
{
    protected $table = 'menu_productos';

    protected $fillable = [
        'menu_categoria_id',
        'nombre',
        'descripcion',
        'precio',
        'imagen',
        'disponible',
        'activo',
        'orden',
        'tiempo_preparacion',
    ];

    protected $casts = [
        'precio'              => 'decimal:2',
        'disponible'          => 'boolean',
        'activo'              => 'boolean',
        'orden'               => 'integer',
        'tiempo_preparacion'  => 'integer',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(MenuCategoria::class, 'menu_categoria_id');
    }

    // Scope para productos disponibles
    public function scopeDisponibles($query)
    {
        return $query->where('activo', true)->where('disponible', true)->orderBy('orden');
    }
}// Relación recetas - agregado automáticamente
