<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Evento de checkout en caja: un carrito, un pago, un cliente.
 * Agrupa 1..N "ventas" (comprobantes), una por cada empresa presente
 * en el carrito.
 */
class VentaGrupo extends Model
{
    use Auditable;

    protected $auditModulo = 'Ventas';
    protected $table = 'venta_grupos';

    protected $fillable = [
        'tienda_id',
        'sesion_caja_id',
        'usuario_id',
        'cliente_id',
        'cliente_tipo_doc',
        'cliente_num_doc',
        'cliente_razon_social',
        'cliente_direccion',
        'cliente_email',
        'forma_pago',
        'metodo_pago',
        'total_general',
        'monto_pagado',
        'vuelto',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'total_general' => 'decimal:2',
        'monto_pagado'  => 'decimal:2',
        'vuelto'        => 'decimal:2',
    ];

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class);
    }

    public function sesionCaja(): BelongsTo
    {
        return $this->belongsTo(SesionCaja::class, 'sesion_caja_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'venta_grupo_id');
    }

    /**
     * Reparte el monto pagado en el checkout entre los comprobantes del
     * grupo, en proporción al subtotal de cada uno.
     */
    public function repartirPago(): void
    {
        $ventas = $this->ventas;
        $totalGrupo = $ventas->sum('total');
        if ($totalGrupo <= 0) {
            return;
        }

        foreach ($ventas as $venta) {
            $proporcion = $venta->total / $totalGrupo;
            $venta->update([
                'monto_pagado' => round($this->monto_pagado * $proporcion, 2),
                'vuelto'       => round($this->vuelto * $proporcion, 2),
            ]);
        }
    }
}
