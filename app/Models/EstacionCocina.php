<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstacionCocina extends Model
{
    protected $table = 'estaciones_cocina';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'descripcion',
        'estado',
        'orden',
        'tiempo_estimado_minutos',
        'requiere_validacion',
    ];

    protected $casts = [
        'requiere_validacion' => 'boolean',
    ];

    // Relaciones
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function comandas(): HasMany
    {
        return $this->hasMany(ComandaCocina::class, 'estacion_cocina_id');
    }

    // Comandas pendientes en esta estación
    public function comandasPendientes()
    {
        return $this->comandas()
            ->whereIn('estado', ['pendiente', 'aceptada', 'en_preparacion'])
            ->orderBy('prioridad', 'desc')
            ->orderBy('hora_creacion', 'asc');
    }

    // Comandas listas en esta estación
    public function comandasListas()
    {
        return $this->comandas()
            ->where('estado', 'lista')
            ->orderBy('hora_terminacion', 'asc');
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }
}
