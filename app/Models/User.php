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
        'tienda_id',
        'sucursal_id',
        'name',
        'username',
        'email',
        'password',
        'rol',
        'dni',
        'telefono',
        'fecha_ingreso',
        'activo',
        'puede_facturar',
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
        'puede_facturar'    => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Tienda física donde trabaja el usuario. NULL = acceso a todas las
     * tiendas (dueño/admin).
     */
    public function tienda()
    {
        return $this->belongsTo(Tienda::class);
    }

    /**
     * Sucursal (restaurante) donde trabaja el usuario. NULL = acceso a
     * todas las sucursales de la empresa (admin).
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
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

    public function esSuperAdmin(): bool
    {
        return $this->rol === 'superadmin';
    }

    public function esMozo(): bool
    {
        return $this->rol === 'mozo';
    }

    /**
     * Permiso para emitir comprobantes (boleta/factura). Independiente del
     * rol: hoy solo se habilita a usuarios puntuales dentro de una empresa
     * (p.ej. notaría), no a un rol completo.
     */
    public function puedeFacturar(): bool
    {
        return (bool) $this->puede_facturar;
    }

    /**
     * Todos los valores de tipo_acto que existen en el wizard de Expedientes
     * (resources/js/Pages/Notaria/Actos/Index.vue, constante `tiposActo`).
     * Sin whitelist en BD (columna VARCHAR libre) — esta es la única fuente
     * de verdad de "qué tipos existen", usada para calcular accesos por
     * exclusión (ver rol 'escrituras' abajo).
     */
    const TODOS_TIPOS_ACTO = [
        'compra_venta', 'compra_venta_bien_futuro', 'compra_venta_hipoteca', 'compra_venta_alicuotas',
        'aclaracion_compra_venta', 'ratificacion_compra_venta', 'contrato_preparatorio', 'adjudicacion',
        'rectificacion_area', 'particion', 'prescripcion_dominio',
        'donacion_inmueble', 'donacion_alicuotas', 'donacion_vehiculo',
        'transferencia_vehicular',
        'hipoteca', 'mutuo_hipoteca',
        'poder', 'ampliacion_poder', 'revocatoria_poder',
        'constitucion_sac', 'constitucion_srl', 'constitucion_asociacion', 'aumento_capital', 'transformacion_empresa',
        'sustitucion_regimen', 'cese_regimen',
        'testamento', 'reconocimiento_paternidad', 'autorizacion_viaje', 'autorizacion_viaje_ext', 'divorcio',
        'sucesion_intestada', 'certificacion_notarial', 'legalizacion', 'escritura_publica', 'notificacion',
        'certificado_domiciliario', 'acta_no_contenciosa', 'arrendamiento', 'carta_notarial', 'otro',
    ];

    public function tipoActoPermitidos(): ?array
    {
        return match($this->rol) {
            "admin", "notario" => null,
            "escrituras" => array_values(array_diff(self::TODOS_TIPOS_ACTO, ["prescripcion_dominio", "transferencia_vehicular"])),
            "asistente" => ["legalizacion"],
            "prescripciones" => ["prescripcion_dominio", "escritura_publica"],
            "legalizaciones" => ["legalizacion", "certificacion_notarial"],
            "notificaciones" => ["notificacion", "certificado_domiciliario"],
            "mixto" => ["legalizacion", "certificacion_notarial", "transferencia_vehicular", "escritura_publica"],
            default => [],
        };
    }
}
