<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    protected $table = 'mesas';

    protected $fillable = [
        'empresa_id',
        'numero',
        'nombre',
        'capacidad',
        'zona',
        'estado',
        'mozo_id',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function mozo()
    {
        return $this->belongsTo(User::class, 'mozo_id');
    }

    public function getColorEstadoAttribute(): string
    {
        return match($this->estado) {
            'libre'     => '#10B981',
            'ocupada'   => '#EF4444',
            'reservada' => '#F59E0B',
            'bloqueada' => '#6B7280',
            default     => '#10B981',
        };
    }
}
