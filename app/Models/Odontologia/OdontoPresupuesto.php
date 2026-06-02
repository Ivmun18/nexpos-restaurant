<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoPresupuesto extends Model {
    protected $table = 'odonto_presupuestos';
    protected $fillable = ['empresa_id','paciente_id','doctor_id','fecha','estado','total','observaciones'];
    public function paciente() { return $this->belongsTo(OdontoPaciente::class, 'paciente_id'); }
    public function doctor() { return $this->belongsTo(OdontoDoctor::class, 'doctor_id'); }
    public function items() { return $this->hasMany(OdontoPresupuestoItem::class, 'presupuesto_id'); }
}
