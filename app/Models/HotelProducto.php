<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelProducto extends Model
{
    protected $table = 'hotel_productos';
    protected $fillable = [
        'empresa_id', 'nombre', 'descripcion', 'categoria',
        'precio', 'stock', 'controla_stock', 'activo'
    ];
    protected $casts = [
        'precio'          => 'decimal:2',
        'controla_stock'  => 'boolean',
        'activo'          => 'boolean',
    ];
}
