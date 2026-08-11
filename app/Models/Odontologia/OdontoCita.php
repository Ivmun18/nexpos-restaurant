<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoCita extends Model {
    protected $table = 'odonto_citas';
    public function consultorio() { return $this->belongsTo(OdontoConsultorio::class, 'consultorio_id'); }
    protected $fillable = ['empresa_id','paciente_id','doctor_id','fecha_hora','duracion_min','estado','motivo','observaciones','consultorio_id'];
    public function paciente() { return $this->belongsTo(OdontoPaciente::class, 'paciente_id'); }
    public function doctor() { return $this->belongsTo(OdontoDoctor::class, 'doctor_id'); }
}
