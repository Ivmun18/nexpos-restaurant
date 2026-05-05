<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'caja';

    protected $fillable = [
        'empresa_id',
        'codigo',
        'nombre',
        'responsable_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function sesiones()
    {
        return $this->hasMany(SesionCaja::class);
    }

    public function sesionActiva()
    {
        return $this->hasOne(SesionCaja::class)->where('estado', 'abierta');
    }
}