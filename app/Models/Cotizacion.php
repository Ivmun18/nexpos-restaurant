<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';

    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'cliente_id',
        'numero',
        'fecha_emision',
        'fecha_vencimiento',
        'cliente_tipo_doc',
        'cliente_num_doc',
        'cliente_razon_social',
        'cliente_direccion',
        'cliente_email',
        'moneda',
        'total_gravado',
        'total_exonerado',
        'total_igv',
        'total',
        'estado',
        'observaciones',
        'terminos_condiciones',
    ];

    protected $casts = [
        'fecha_emision'    => 'date',
        'fecha_vencimiento'=> 'date',
        'total'            => 'decimal:2',
        'total_igv'        => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalle()
    {
        return $this->hasMany(CotizacionDetalle::class);
    }
}