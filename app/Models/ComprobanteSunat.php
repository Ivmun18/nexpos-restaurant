<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComprobanteSunat extends Model
{
    protected $table = 'comprobantes_sunat';

    protected $fillable = [
        'empresa_id',
        'caja_restaurante_id',
        'tipo_comprobante',
        'serie',
        'numero',
        'fecha_emision',
        'cliente_tipo_documento',
        'cliente_numero_documento',
        'cliente_nombre',
        'cliente_direccion',
        'cliente_email',
        'total_gravada',
        'total_igv',
        'total',
        'aceptada_por_sunat',
        'sunat_descripcion',
        'codigo_hash',
        'enlace_pdf',
        'enlace_xml',
        'enlace_cdr',
        'estado',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'total_gravada' => 'decimal:2',
        'total_igv' => 'decimal:2',
        'total' => 'decimal:2',
        'aceptada_por_sunat' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(CajaRestaurante::class, 'caja_restaurante_id');
    }

    public function getTipoComprobanteNombreAttribute(): string
    {
        return match($this->tipo_comprobante) {
            '01' => 'Factura',
            '03' => 'Boleta',
            '07' => 'Nota de Crédito',
            default => 'Desconocido',
        };
    }

    public function getNumeroCompletoAttribute(): string
    {
        return $this->serie . '-' . str_pad($this->numero, 8, '0', STR_PAD_LEFT);
    }
}
