<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CategoriaMinimarket;

class Producto extends Model
{
    protected $table = 'productos';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($producto) {
            if (empty($producto->codigo)) {
                $ultimo = static::where('empresa_id', $producto->empresa_id)
                    ->whereNotNull('codigo')
                    ->orderByDesc('id')->first();
                $numero = $ultimo ? ((int) preg_replace('/[^0-9]/', '', $ultimo->codigo) + 1) : 1;
                $producto->codigo = 'PROD-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

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
        'fecha_vencimiento',
        'dias_alerta_vencimiento',
        'lote',
        'laboratorio',
        'principio_activo',
        'presentacion',
        'concentracion',
        'requiere_receta',
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
