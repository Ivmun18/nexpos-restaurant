<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActoNotarial extends Model
{
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

    public static function generarNumero($empresaId)
    {
        $ultimo = static::where('empresa_id', $empresaId)->max('id') ?? 0;
        return 'EXP-' . now()->year . '-' . str_pad($ultimo + 1, 5, '0', STR_PAD_LEFT);
    }
}
