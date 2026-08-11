<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrasladoMercaderia extends Model
{
    protected $table = 'traslados_mercaderia';

    protected $fillable = [
        'empresa_id', 'usuario_id', 'local_destino', 'transportista', 'placa_vehiculo', 'observaciones',
    ];

    public function detalle()
    {
        return $this->hasMany(TrasladoMercaderiaDetalle::class, 'traslado_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
