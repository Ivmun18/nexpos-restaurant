<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $fillable = [
        'mesa_id', 'user_id', 'estado',
        'numero_ronda', 'notas', 'total',
    ];

    protected $casts = [
        'total'        => 'decimal:2',
        'numero_ronda' => 'integer',
    ];

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(PedidoDetalle::class);
    }

    public function recalcularTotal(): void
    {
        $this->update([
            'total' => $this->detalles()->sum('subtotal')
        ]);
    }
}