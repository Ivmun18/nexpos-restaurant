<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SesionCaja extends Model
{
    protected $table = 'sesiones_caja';

    protected $fillable = [
        'caja_id',
        'usuario_id',
        'fecha_apertura',
        'monto_apertura',
        'fecha_cierre',
        'monto_cierre_sistema',
        'monto_cierre_real',
        'diferencia',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre'   => 'datetime',
        'monto_apertura' => 'decimal:2',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function movimientos()
    {
        return $this->hasMany(CajaMovimiento::class, 'sesion_id');
    }

    public function getTotalIngresosAttribute()
    {
        return $this->movimientos->where('tipo', 'ingreso')->sum('monto');
    }

    public function getTotalEgresosAttribute()
    {
        return $this->movimientos->where('tipo', 'egreso')->sum('monto');
    }

    public function getSaldoActualAttribute()
    {
        return $this->monto_apertura + $this->total_ingresos - $this->total_egresos;
    }
}