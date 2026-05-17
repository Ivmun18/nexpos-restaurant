<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaMinimarket extends Model
{
    protected $table = 'cajas_minimarket';

    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'monto_inicial',
        'monto_inicial_original',
        'total_efectivo',
        'total_yape',
        'total_plin',
        'total_tarjeta',
        'total_ventas',
        'cantidad_ventas',
        'monto_final',
        'monto_final_original',
        'diferencia',
        'observaciones',
        'motivo_correccion',
        'corregido_por_id',
        'corregido_at',
        'estado',
        'apertura_at',
        'cierre_at',
    ];

    protected $casts = [
        'apertura_at'  => 'datetime',
        'cierre_at'    => 'datetime',
        'corregido_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function corregidoPor()
    {
        return $this->belongsTo(User::class, 'corregido_por_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function fueCorregida(): bool
    {
        return $this->corregido_at !== null;
    }
}
