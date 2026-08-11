<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrasladoMercaderiaDetalle extends Model
{
    protected $table = 'traslados_mercaderia_detalle';

    protected $fillable = [
        'traslado_id', 'producto_id', 'cantidad', 'stock_anterior', 'stock_nuevo',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function traslado()
    {
        return $this->belongsTo(TrasladoMercaderia::class, 'traslado_id');
    }
}
