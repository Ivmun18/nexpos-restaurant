<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $fillable = [
        'empresa_id',
        'tipo_documento',
        'numero_documento',
        'razon_social',
        'nombre_comercial',
        'ubigeo',
        'direccion',
        'distrito',
        'provincia',
        'departamento',
        'pais_codigo',
        'telefono',
        'email',
        'contacto_nombre',
        'dias_credito',
        'agente_retencion',
        'activo',
    ];

    protected $casts = [
        'agente_retencion' => 'boolean',
        'activo'           => 'boolean',
    ];

    public function getTipoDocumentoLabelAttribute()
    {
        return match($this->tipo_documento) {
            '6' => 'RUC',
            '1' => 'DNI',
            default => 'Otro',
        };
    }
}