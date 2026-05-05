<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CajaRestaurante extends Model
{
    protected $table = 'caja_restaurante';

    protected $fillable = [
        'mesa_id', 'user_id', 'total',
        'monto_pagado', 'vuelto', 'metodo_pago', 'notas',
    ];

    protected $casts = [
        'total'        => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'vuelto'       => 'decimal:2',
    ];

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comprobante(): HasOne
    {
        return $this->hasOne(ComprobanteSunat::class, 'caja_restaurante_id');
    }
}
