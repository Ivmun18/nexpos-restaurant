<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransferenciaStock extends Model
{
    use Auditable;

    protected $auditModulo = 'TransferenciasStock';
    protected $table = 'transferencias_stock';

    protected $fillable = [
        'tienda_origen_id',
        'tienda_destino_id',
        'usuario_id',
        'fecha',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function tiendaOrigen(): BelongsTo
    {
        return $this->belongsTo(Tienda::class, 'tienda_origen_id');
    }

    public function tiendaDestino(): BelongsTo
    {
        return $this->belongsTo(Tienda::class, 'tienda_destino_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalle(): HasMany
    {
        return $this->hasMany(TransferenciaStockDetalle::class, 'transferencia_id');
    }
}
