<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ComandaCocina extends Model
{
    protected $table = 'comandas_cocina';

    protected $fillable = [
        'empresa_id',
        'pedido_id',
        'pedido_detalle_id',
        'estacion_cocina_id',
        'user_id',
        'estado',
        'nombre_producto',
        'cantidad',
        'notas',
        'hora_creacion',
        'hora_aceptacion',
        'hora_inicio_preparacion',
        'hora_terminacion',
        'hora_entrega_mozo',
        'tiempo_preparacion_minutos',
        'numero_ronda',
        'es_urgente',
        'prioridad',
        'chef_asignado_id',
        'motivo_rechazo',
    ];

    protected $casts = [
        'es_urgente' => 'boolean',
        'hora_creacion' => 'datetime',
        'hora_aceptacion' => 'datetime',
        'hora_inicio_preparacion' => 'datetime',
        'hora_terminacion' => 'datetime',
        'hora_entrega_mozo' => 'datetime',
    ];

    // Relaciones
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function pedidoDetalle(): BelongsTo
    {
        return $this->belongsTo(PedidoDetalle::class);
    }

    public function estacion(): BelongsTo
    {
        return $this->belongsTo(EstacionCocina::class, 'estacion_cocina_id');
    }

    public function mozo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function chef(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chef_asignado_id');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeEnPreparacion($query)
    {
        return $query->where('estado', 'en_preparacion');
    }

    public function scopeListas($query)
    {
        return $query->where('estado', 'lista');
    }

    public function scopeEntregadas($query)
    {
        return $query->where('estado', 'entregada');
    }

    public function scopePorEstacion($query, $estacionId)
    {
        return $query->where('estacion_cocina_id', $estacionId);
    }

    public function scopePorEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    // Métodos
    public function aceptar()
    {
        $this->update([
            'estado' => 'aceptada',
            'hora_aceptacion' => Carbon::now(),
        ]);
        return $this;
    }

    public function iniciarPreparacion()
    {
        $this->update([
            'estado' => 'en_preparacion',
            'hora_inicio_preparacion' => Carbon::now(),
        ]);
        return $this;
    }

    public function marcarLista()
    {
        $ahora = Carbon::now();
        $tiempoPreparacion = null;

        if ($this->hora_inicio_preparacion) {
            $tiempoPreparacion = $this->hora_inicio_preparacion->diffInMinutes($ahora);
        }

        $this->update([
            'estado' => 'lista',
            'hora_terminacion' => $ahora,
            'tiempo_preparacion_minutos' => $tiempoPreparacion,
        ]);

        return $this;
    }

    public function entregarAlMozo()
    {
        $this->update([
            'estado' => 'entregada',
            'hora_entrega_mozo' => Carbon::now(),
        ]);

        // Verificar si todos los detalles del pedido están entregados
        $this->pedido->verificarSiTodoEstaEntregado();

        return $this;
    }

    public function rechazar($motivo = null)
    {
        $this->update([
            'estado' => 'rechazada',
            'motivo_rechazo' => $motivo,
        ]);
        return $this;
    }

    // Obtener tiempo en cocina (desde que se creó hasta ahora o hasta que se terminó)
    public function getTiempoEnCocinaAttribute()
    {
        $ahora = $this->hora_terminacion ?? Carbon::now();
        return $this->hora_creacion->diffInMinutes($ahora);
    }

    // Obtener estado legible
    public function getEstadoLegibleAttribute()
    {
        return match($this->estado) {
            'pendiente' => 'Pendiente',
            'aceptada' => 'Aceptada',
            'en_preparacion' => 'En Preparación',
            'lista' => 'Lista',
            'entregada' => 'Entregada',
            'rechazada' => 'Rechazada',
            default => 'Desconocido',
        };
    }

    // Color del estado para UI
    public function getColorEstadoAttribute()
    {
        return match($this->estado) {
            'pendiente' => 'bg-yellow-100 text-yellow-800',
            'aceptada' => 'bg-blue-100 text-blue-800',
            'en_preparacion' => 'bg-orange-100 text-orange-800',
            'lista' => 'bg-green-100 text-green-800',
            'entregada' => 'bg-gray-100 text-gray-800',
            'rechazada' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
