<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'empresa_id',
        'name',
        'email',
        'password',
        'rol',
        'dni',
        'telefono',
        'fecha_ingreso',
        'activo',
        'observaciones',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'fecha_ingreso'     => 'date',
        'activo'            => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Mesas asignadas a este mozo.
     */
    public function mesas(): HasMany
    {
        return $this->hasMany(Mesa::class, 'mozo_id');
    }

    /**
     * Pedidos atendidos por este mozo.
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    /**
     * Turnos de este usuario.
     */
    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }

    /**
     * Scope: solo mozos.
     */
    public function scopeMozos(Builder $query): Builder
    {
        return $query->where('rol', 'mozo');
    }

    /**
     * Scope: solo activos.
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: por empresa actual del usuario logueado.
     */
    public function scopeDeEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function esVendedor(): bool
    {
        return $this->rol === 'vendedor';
    }

    public function esCajero(): bool
    {
        return $this->rol === 'cajero';
    }

    public function esContador(): bool
    {
        return $this->rol === 'contador';
    }

    public function esMozo(): bool
    {
        return $this->rol === 'mozo';
    }
}