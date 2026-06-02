<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoPago extends Model {
    protected $table = 'odonto_pagos';
    protected $fillable = ['empresa_id','paciente_id','presupuesto_id','fecha','monto_total','num_cuotas','tipo_pago','estado','observaciones'];
    public function paciente() { return $this->belongsTo(OdontoPaciente::class, 'paciente_id'); }
    public function presupuesto() { return $this->belongsTo(OdontoPresupuesto::class, 'presupuesto_id'); }
    public function cuotas() { return $this->hasMany(OdontoPagoCuota::class, 'pago_id'); }
}
