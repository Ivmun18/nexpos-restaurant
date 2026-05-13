<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsumoMovimiento extends Model
{
    protected $fillable = [
        'insumo_id', 'user_id', 'tipo', 'cantidad',
        'costo_unitario', 'stock_anterior', 'stock_nuevo', 'motivo', 'compra_id',
    ];

    protected $casts = [
        'cantidad'       => 'decimal:3',
        'costo_unitario' => 'decimal:4',
        'stock_anterior' => 'decimal:3',
        'stock_nuevo'    => 'decimal:3',
    ];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
