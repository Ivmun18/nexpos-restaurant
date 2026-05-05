<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaCreditoDetalle extends Model
{
    protected $table = 'nota_credito_detalle';

    protected $fillable = [
        'nota_credito_id',
        'producto_id',
        'linea',
        'descripcion',
        'unidad_medida',
        'cantidad',
        'precio_unitario',
        'valor_unitario',
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

    public function notaCredito()
    {
        return $this->belongsTo(NotaCredito::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
