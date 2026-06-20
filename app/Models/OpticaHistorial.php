<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OpticaHistorial extends Model {
    protected $table = 'optica_historial_clinico';
    protected $fillable = ['empresa_id','paciente_id','doctor_id','ficha_id','fecha','motivo_consulta','antecedentes','diagnostico','tratamiento','observaciones','proxima_cita'];
    protected $casts = ['fecha'=>'date','proxima_cita'=>'date'];
    public function paciente() { return $this->belongsTo(OpticaPaciente::class,'paciente_id'); }
    public function doctor() { return $this->belongsTo(OpticaDoctor::class,'doctor_id'); }
    public function ficha() { return $this->belongsTo(OpticaFicha::class,'ficha_id'); }
}
