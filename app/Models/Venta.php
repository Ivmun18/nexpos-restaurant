<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'cliente_id',
        'tipo_comprobante',
        'serie',
        'correlativo',
        'numero_completo',
        'fecha_emision',
        'hora_emision',
        'cliente_tipo_doc',
        'cliente_num_doc',
        'cliente_razon_social',
        'cliente_direccion',
        'cliente_email',
        'moneda',
        'tipo_cambio',
        'total_gravado',
        'total_exonerado',
        'total_inafecto',
        'total_descuento',
        'total_igv',
        'total_isc',
        'total_icbper',
        'total',
        'forma_pago',
        'monto_pagado',
        'vuelto',
        'estado',
        'observaciones',
        'metodo_pago',
        'nubefact_id',
        'nubefact_estado',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'total'         => 'decimal:2',
        'total_igv'     => 'decimal:2',
        'total_gravado' => 'decimal:2',
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
        return $this->hasMany(VentaDetalle::class);
    }

    public function getTipoComprobanteNombreAttribute()
    {
        return match($this->tipo_comprobante) {
            '01' => 'Factura',
            '03' => 'Boleta',
            '07' => 'Nota de crédito',
            '08' => 'Nota de débito',
            default => 'Otro',
        };
    }
}