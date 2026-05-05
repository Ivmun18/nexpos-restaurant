<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaCredito extends Model
{
    protected $table = 'notas_credito';

    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'venta_id',
        'tipo_comprobante',
        'serie',
        'correlativo',
        'numero_completo',
        'fecha_emision',
        'doc_ref_tipo',
        'doc_ref_serie',
        'doc_ref_correlativo',
        'doc_ref_numero',
        'motivo_codigo',
        'motivo_descripcion',
        'cliente_tipo_doc',
        'cliente_num_doc',
        'cliente_razon_social',
        'moneda',
        'tipo_cambio',
        'total_gravado',
        'total_exonerado',
        'total_igv',
        'total',
        'estado',
        'fecha_envio_sunat',
        'observaciones',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'total'         => 'decimal:2',
        'total_igv'     => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalle()
    {
        return $this->hasMany(NotaCreditoDetalle::class);
    }

    public function getMotivosDisponibles(): array
    {
        return [
            '01' => 'Anulación de la operación',
            '02' => 'Anulación por error en el RUC',
            '03' => 'Corrección por error en la descripción',
            '04' => 'Descuento global',
            '05' => 'Descuento por ítem',
            '06' => 'Devolución total',
            '07' => 'Devolución por ítem',
            '08' => 'Bonificación',
            '09' => 'Disminución en el valor',
            '10' => 'Otros conceptos',
        ];
    }
}
