<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    protected $table = 'venta_detalle';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'linea',
        'codigo_producto',
        'descripcion',
        'lote',
        'fecha_vencimiento',
        'unidad_medida',
        'cantidad',
        'precio_unitario',
        'valor_unitario',
        'descuento_monto',
        'tipo_afectacion_igv',
        'total_valor_venta',
        'total_igv',
        'total',
    ];

    protected $casts = [
        'cantidad'        => 'decimal:3',
        'precio_unitario' => 'decimal:4',
        'total'           => 'decimal:2',
        'total_igv'       => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}