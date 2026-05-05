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
        'total_efectivo',
        'total_yape',
        'total_plin',
        'total_tarjeta',
        'total_ventas',
        'cantidad_ventas',
        'monto_final',
        'diferencia',
        'observaciones',
        'estado',
        'apertura_at',
        'cierre_at',
    ];

    protected $casts = [
        'apertura_at' => 'datetime',
        'cierre_at'   => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}