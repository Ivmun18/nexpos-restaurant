<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Turno extends Model
{
    protected $fillable = [
        'user_id', 'tipo', 'nombre', 'estado',
        'apertura', 'cierre', 'total_ventas', 'total_mesas', 'notas',
    ];

    protected $casts = [
        'apertura'     => 'datetime',
        'cierre'       => 'datetime',
        'total_ventas' => 'decimal:2',
        'total_mesas'  => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function duracion(): string
    {
        $fin = $this->cierre ?? now();
        $diff = $this->apertura->diff($fin);
        return $diff->h . 'h ' . $diff->i . 'min';
    }
}