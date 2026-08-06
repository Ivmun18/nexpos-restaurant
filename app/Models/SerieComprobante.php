<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerieComprobante extends Model
{
    protected $table = 'series_comprobante';

    protected $fillable = [
        'empresa_id',
        'tienda_id',
        'tipo_comprobante',
        'serie',
        'ultimo_correlativo',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class);
    }

    public function siguienteCorrelativo(): int
    {
        $this->increment('ultimo_correlativo');
        return $this->ultimo_correlativo;
    }
}
