<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Modificador extends Model
{
    protected $table = 'modificadores';

    protected $fillable = [
        'empresa_id',
        'categoria_id',
        'nombre',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(MenuCategoria::class, 'categoria_id');
    }

    public function scopeDeEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }
}
