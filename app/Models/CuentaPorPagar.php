<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuentaPorPagar extends Model
{
    use SoftDeletes;

    protected $table = 'cuentas_por_pagar';

    protected $fillable = [
        'empresa_id',
        'proveedor_id',
        'compra_id',
        'numero_documento',
        'monto_total',
        'monto_pagado',
        'monto_pendiente',
        'fecha_vencimiento',
        'fecha_emision',
        'estado',
        'forma_pago',
        'observaciones',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'monto_pendiente' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'fecha_emision' => 'date',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function pagos()
    {
        return $this->hasMany(PagoProveedor::class);
    }

    // Scopes útiles
    public function scopePendiente($query)
    {
        return $query->whereIn('estado', ['pendiente', 'parcial', 'vencido']);
    }

    public function scopeVencido($query)
    {
        return $query->where('estado', 'vencido')
            ->orWhere(function ($q) {
                $q->whereIn('estado', ['pendiente', 'parcial'])
                    ->where('fecha_vencimiento', '<', now()->toDateString());
            });
    }

    public function scopePorVencer($query)
    {
        return $query->whereIn('estado', ['pendiente', 'parcial'])
            ->whereBetween('fecha_vencimiento', [now()->toDateString(), now()->addDays(7)->toDateString()]);
    }
}
