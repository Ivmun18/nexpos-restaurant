<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndiceNotarial extends Model
{
    protected $table = 'indice_notarial';
    
    protected $fillable = [
        'empresa_id',
        'acto_id',
        'numero_indice',
        'numero_correlativo',
        'anio',
        'tipo_acto',
        'asunto',
        'partes',
        'monto',
        'fecha_otorgamiento',
        'fecha_registro',
        'usuario_registro_id',
        'cerrado',
        'hash',
    ];

    protected $casts = [
        'fecha_otorgamiento' => 'datetime',
        'fecha_registro' => 'datetime',
        'cerrado' => 'boolean',
        'monto' => 'decimal:2',
    ];

    // Relaciones
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function acto()
    {
        return $this->belongsTo(ActoNotarial::class);
    }

    public function usuarioRegistro()
    {
        return $this->belongsTo(User::class, 'usuario_registro_id');
    }

    // Generar número de índice automático
    public static function generarNumeroIndice($empresaId, $anio = null)
    {
        $anio = $anio ?? now()->year;
        
        $ultimo = self::where('empresa_id', $empresaId)
            ->where('anio', $anio)
            ->max('numero_correlativo') ?? 0;
        
        $nuevo = $ultimo + 1;
        
        return [
            'numero_indice' => $anio . '-' . str_pad($nuevo, 5, '0', STR_PAD_LEFT),
            'numero_correlativo' => $nuevo,
            'anio' => $anio,
        ];
    }

    // Generar hash para integridad
    public function generarHash()
    {
        $data = implode('|', [
            $this->numero_indice,
            $this->fecha_otorgamiento,
            $this->tipo_acto,
            $this->asunto,
            $this->partes,
            $this->monto,
        ]);
        
        return hash('sha256', $data);
    }

    // Registrar en el índice (desde un acto)
    public static function registrarActo(ActoNotarial $acto)
    {
        // Si ya está registrado, no hacer nada
        if (self::where('acto_id', $acto->id)->exists()) {
            return null;
        }

        $numeracion = self::generarNumeroIndice($acto->empresa_id);
        
        // Obtener nombres de las partes
        $partesTexto = $acto->partes->map(function($parte) {
            return $parte->rol . ': ' . $parte->nombre_completo;
        })->join(' | ');
        
        // Si no hay partes estructuradas, usar campo legacy
        if (empty($partesTexto)) {
            $partesTexto = $acto->partes_intervinientes ?? 'Sin partes registradas';
        }

        $registro = self::create([
            'empresa_id' => $acto->empresa_id,
            'acto_id' => $acto->id,
            'numero_indice' => $numeracion['numero_indice'],
            'numero_correlativo' => $numeracion['numero_correlativo'],
            'anio' => $numeracion['anio'],
            'tipo_acto' => $acto->tipo_acto,
            'asunto' => $acto->asunto,
            'partes' => $partesTexto,
            'monto' => $acto->monto_cobrar,
            'fecha_otorgamiento' => $acto->fecha_ingreso,
            'fecha_registro' => now(),
            'usuario_registro_id' => $acto->usuario_id,
        ]);

        // Generar hash
        $registro->hash = $registro->generarHash();
        $registro->save();

        return $registro;
    }

    // Scope para búsquedas
    public function scopeBuscar($query, $buscar)
    {
        return $query->where(function($q) use ($buscar) {
            $q->where('numero_indice', 'like', "%{$buscar}%")
              ->orWhere('asunto', 'like', "%{$buscar}%")
              ->orWhere('partes', 'like', "%{$buscar}%");
        });
    }

    public function scopePorAnio($query, $anio)
    {
        return $query->where('anio', $anio);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_acto', $tipo);
    }

    public function scopeNoCerrados($query)
    {
        return $query->where('cerrado', false);
    }
}
