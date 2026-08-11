<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoPresentacion extends Model
{
    protected $table = 'producto_presentaciones';

    protected $fillable = [
        'producto_id',
        'nombre',
        'unidad_sunat',
        'factor_conversion',
        'precio_venta',
        'codigo_barras',
        'es_default',
        'activo',
    ];

    protected $casts = [
        'factor_conversion' => 'decimal:4',
        'precio_venta'      => 'decimal:2',
        'es_default'        => 'boolean',
        'activo'            => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
