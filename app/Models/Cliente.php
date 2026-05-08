<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $casts = [
        'numero_documento' => 'string',
        'limite_credito' => 'float',
        'descuento_porcentaje' => 'float',
    ];

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
        'celular',
        'email',
        'limite_credito',
        'dias_credito',
        'descuento_porcentaje',
        'activo',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function getTipoDocumentoLabelAttribute()
    {
        return match($this->tipo_documento) {
            '1' => 'DNI',
            '6' => 'RUC',
            '4' => 'Carnet extranjería',
            '7' => 'Pasaporte',
            default => 'Otro',
        };
    }
}