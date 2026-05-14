<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActoPago extends Model
{
    protected $table = 'acto_pagos';
    protected $fillable = ['acto_id', 'usuario_id', 'monto', 'metodo_pago', 'tipo', 'referencia', 'observaciones'];
    protected $casts = ['monto' => 'decimal:2'];
    public function acto() { return $this->belongsTo(ActoNotarial::class, 'acto_id'); }
    public function usuario() { return $this->belongsTo(User::class, 'usuario_id'); }
}
