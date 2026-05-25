<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoDetalle extends Model
{
    protected $fillable = [
        'pedido_id', 'menu_producto_id', 'nombre_producto',
        'cantidad', 'precio_unitario', 'subtotal', 'notas', 'estado',
        'pagado', 'caja_detalle_id',
        'anulado', 'motivo_anulacion', 'anulado_por', 'anulado_at',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
        'cantidad'        => 'integer',
        'pagado'          => 'boolean',
        'anulado'         => 'boolean',
        'anulado_at'      => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(MenuProducto::class, 'menu_producto_id');
    }
}