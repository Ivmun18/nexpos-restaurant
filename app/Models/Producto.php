<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CategoriaMinimarket;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'empresa_id',
        'categoria_id',
        'codigo',
        'codigo_sunat',
        'codigo_barras',
        'descripcion',
        'descripcion_corta',
        'tipo',
        'unidad_medida',
        'precio_venta',
        'precio_compra',
        'precio_minimo',
        'afecto_igv',
        'afecto_isc',
        'afecto_icbper',
        'tipo_afectacion_igv',
        'tasa_isc',
        'controla_stock',
        'stock_minimo',
        'stock_actual',
        'activo',
    ];

    protected $casts = [
        'precio_venta'  => 'decimal:4',
        'precio_compra' => 'decimal:4',
        'afecto_igv'    => 'boolean',
        'controla_stock'=> 'boolean',
        'activo'        => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaMinimarket::class, 'categoria_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function getPrecioConIgvAttribute()
    {
        if ($this->afecto_igv) {
            return round($this->precio_venta * 1.18, 2);
        }
        return $this->precio_venta;
    }
}
