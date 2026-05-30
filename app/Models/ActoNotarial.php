<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class ActoNotarial extends Model
{
    use Auditable;

    protected $auditModulo = 'Notaria';
    protected $table = 'actos_notariales';

    protected $fillable = [
        'empresa_id', 'numero_expediente', 'tipo_acto', 'asunto',
        'cliente_id', 'usuario_id', 'estado', 'fecha_ingreso',
        'fecha_entrega', 'monto_cobrar', 'monto_pagado', 'estado_pago',
        'observaciones', 'partes_intervinientes',
    ];

    protected $casts = [
        'fecha_ingreso'  => 'date',
        'fecha_entrega'  => 'date',
        'monto_cobrar'   => 'decimal:2',
        'monto_pagado'   => 'decimal:2',
    ];

    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function usuario() { return $this->belongsTo(User::class, 'usuario_id'); }
    public function documentos() { return $this->hasMany(ActoDocumento::class, 'acto_id'); }
    public function seguimientos() { return $this->hasMany(ActoSeguimiento::class, 'acto_id'); }
    public function pagos() { return $this->hasMany(ActoPago::class, 'acto_id'); }
    public function datos() { return $this->hasMany(ActoDato::class, 'acto_id'); }
    public function requisitos() { return $this->hasMany(ActoRequisito::class, 'acto_id'); }
    public function partes() { return $this->hasMany(ActoParte::class, 'acto_id')->orderBy('orden'); }
    public function empresa() { return $this->belongsTo(Empresa::class, 'empresa_id'); }
    public function comprobantes() { return $this->hasMany(\App\Models\ComprobanteSunat::class, 'acto_id'); }
    public static function generarNumero($empresaId)
    {
        $anio = now()->year;
        $ultimo = static::where('empresa_id', $empresaId)
            ->whereYear('created_at', $anio)
            ->count();
        return 'EXP-' . $anio . '-' . str_pad($ultimo + 1, 5, '0', STR_PAD_LEFT);
    }

    // Relación con índice notarial
    public function indiceNotarial()
    {
        return $this->hasOne(IndiceNotarial::class, 'acto_id');
    }
}
