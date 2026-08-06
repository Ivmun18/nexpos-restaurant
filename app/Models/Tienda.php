<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tienda extends Model
{
    use Auditable;

    protected $auditModulo = 'Tiendas';

    protected $fillable = [
        'codigo',
        'nombre',
        'direccion',
        'ubigeo',
        'telefono',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'empresa_tienda');
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function cajas(): HasMany
    {
        return $this->hasMany(Caja::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductoStock::class);
    }

    public function series(): HasMany
    {
        return $this->hasMany(SerieComprobante::class);
    }

    public function ventaGrupos(): HasMany
    {
        return $this->hasMany(VentaGrupo::class);
    }
}
