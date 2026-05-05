<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SunatRespuesta extends Model
{
    protected $table = 'sunat_respuestas';

    protected $fillable = [
        'empresa_id',
        'numero_completo',
        'tipo_comprobante',
        'referencia_id',
        'referencia_tabla',
        'codigo_respuesta',
        'descripcion_respuesta',
        'notas_cdr',
        'xml_cdr',
        'ticket_sunat',
        'fecha_envio',
        'fecha_respuesta',
        'intento',
        'exitoso',
        'error_tecnico',
    ];

    protected $casts = [
        'exitoso'         => 'boolean',
        'fecha_envio'     => 'datetime',
        'fecha_respuesta' => 'datetime',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'referencia_id');
    }
}
