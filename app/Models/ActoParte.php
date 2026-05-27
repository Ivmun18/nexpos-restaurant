<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActoParte extends Model
{
    protected $table = 'acto_partes';

    protected $fillable = [
        'acto_id',
        'tipo_persona',
        'tipo_documento',
        'numero_documento',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'razon_social',
        'rol',
        'orden',
        'estado_civil',
        'regimen_patrimonial',
        'nombre_conyuge',
        'dni_conyuge',
        'domicilio',
        'distrito',
        'provincia',
        'departamento',
        'telefono',
        'email',
        'actua_mediante_representante',
        'nombre_representante',
        'dni_representante',
        'tipo_poder',
        'facultades_representante',
        'profesion',
        'fecha_nacimiento',
        'observaciones',
    ];

    protected $casts = [
        'actua_mediante_representante' => 'boolean',
        'fecha_nacimiento' => 'date',
    ];

    // Relación con el acto notarial
    public function acto(): BelongsTo
    {
        return $this->belongsTo(ActoNotarial::class, 'acto_id');
    }

    // Accessor para nombre completo
    public function getNombreCompletoAttribute(): string
    {
        if ($this->tipo_persona === 'juridica') {
            return $this->razon_social;
        }
        
        return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    // Accessor para tipo de documento label
    public function getTipoDocumentoLabelAttribute(): string
    {
        return match($this->tipo_documento) {
            '1' => 'DNI',
            '6' => 'RUC',
            '4' => 'Carnet de Extranjería',
            '7' => 'Pasaporte',
            default => 'Otro',
        };
    }

    // Roles comunes en actos notariales
    public static function rolesDisponibles(): array
    {
        return [
            // Compraventa
            'vendedor' => 'Vendedor',
            'comprador' => 'Comprador',
            
            // Poderes
            'otorgante' => 'Otorgante (quien da el poder)',
            'apoderado' => 'Apoderado (quien recibe el poder)',
            
            // Testamentos
            'testador' => 'Testador',
            'heredero' => 'Heredero',
            'legatario' => 'Legatario',
            
            // Actas
            'compareciente' => 'Compareciente',
            'solicitante' => 'Solicitante',
            'declarante' => 'Declarante',
            
            // Otros
            'testigo' => 'Testigo',
            'interprete' => 'Intérprete',
            'representante' => 'Representante Legal',
        ];
    }
}
