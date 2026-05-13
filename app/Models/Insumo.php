<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    protected $fillable = [
        'empresa_id', 'nombre', 'categoria', 'unidad_medida',
        'stock_actual', 'stock_minimo', 'precio_promedio', 'activo', 'observaciones',
    ];

    protected $casts = [
        'stock_actual'   => 'decimal:3',
        'stock_minimo'   => 'decimal:3',
        'precio_promedio'=> 'decimal:4',
        'activo'         => 'boolean',
    ];

    public function movimientos()
    {
        return $this->hasMany(InsumoMovimiento::class);
    }

    public function getBajoStockAttribute(): bool
    {
        return $this->stock_actual <= $this->stock_minimo;
    }
}
