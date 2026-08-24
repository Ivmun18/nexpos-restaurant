<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoVarianteOpcion extends Model
{
    protected $table = 'producto_variantes_opcion';

    protected $fillable = [
        'grupo_id',
        'nombre',
        'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(ProductoVarianteGrupo::class, 'grupo_id');
    }
}
