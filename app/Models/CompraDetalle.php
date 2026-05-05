<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{
    protected $table = 'compra_detalle';

    protected $fillable = [
        'compra_id',
        'producto_id',
        'descripcion',
        'unidad_medida',
        'cantidad',
        'precio_unitario',
        'valor_unitario',
        'tipo_afectacion_igv',
        'total_valor',
        'total_igv',
        'total',
    ];

    protected $casts = [
        'cantidad'        => 'decimal:3',
        'precio_unitario' => 'decimal:4',
        'total'           => 'decimal:2',
        'total_igv'       => 'decimal:2',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}