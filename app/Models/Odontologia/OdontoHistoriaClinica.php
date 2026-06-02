<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoHistoriaClinica extends Model {
    protected $table = 'odonto_historia_clinica';
    protected $fillable = ['paciente_id','doctor_id','cita_id','fecha','anamnesis','diagnostico','tratamiento_realizado','observaciones'];
    public function paciente() { return $this->belongsTo(OdontoPaciente::class, 'paciente_id'); }
    public function doctor() { return $this->belongsTo(OdontoDoctor::class, 'doctor_id'); }
}
