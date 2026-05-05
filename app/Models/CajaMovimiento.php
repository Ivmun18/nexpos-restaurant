<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaMovimiento extends Model
{
    protected $table = 'caja_movimientos';

    protected $fillable = [
        'sesion_id',
        'usuario_id',
        'tipo',
        'concepto',
        'referencia_id',
        'monto',
        'observaciones',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function sesion()
    {
        return $this->belongsTo(SesionCaja::class, 'sesion_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
