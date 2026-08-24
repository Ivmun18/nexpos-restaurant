<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductoVarianteGrupo extends Model
{
    protected $table = 'producto_variantes_grupo';

    protected $fillable = [
        'producto_id',
        'nombre',
        'requerido',
        'orden',
    ];

    protected $casts = [
        'requerido' => 'boolean',
        'orden'     => 'integer',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(MenuProducto::class, 'producto_id');
    }

    public function opciones(): HasMany
    {
        return $this->hasMany(ProductoVarianteOpcion::class, 'grupo_id')->orderBy('orden');
    }
}
