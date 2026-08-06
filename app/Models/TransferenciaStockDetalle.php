<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferenciaStockDetalle extends Model
{
    protected $table = 'transferencia_stock_detalle';

    protected $fillable = [
        'transferencia_id',
        'producto_id',
        'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
    ];

    public function transferencia(): BelongsTo
    {
        return $this->belongsTo(TransferenciaStock::class, 'transferencia_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
