<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    protected $fillable = ['menu_producto_id', 'insumo_id', 'cantidad'];

    protected $casts = ['cantidad' => 'decimal:4'];

    public function menuProducto()
    {
        return $this->belongsTo(MenuProducto::class);
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }
}
