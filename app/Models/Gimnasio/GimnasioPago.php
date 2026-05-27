<?php
namespace App\Models\Gimnasio;
use Illuminate\Database\Eloquent\Model;

class GimnasioPago extends Model {
    protected $table = 'gimnasio_pagos';
    protected $fillable = ['empresa_id','miembro_id','plan_id','monto','metodo_pago','referencia','fecha_pago','periodo_inicio','periodo_fin','comprobante_tipo','comprobante_serie','comprobante_numero','estado','usuario_id'];
    protected $casts = ['fecha_pago'=>'date','periodo_inicio'=>'date','periodo_fin'=>'date'];
    public function miembro() { return $this->belongsTo(GimnasioMiembro::class, 'miembro_id'); }
    public function plan() { return $this->belongsTo(GimnasioPlan::class, 'plan_id'); }
}
