<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoOdontograma extends Model {
    protected $table = 'odonto_odontograma';
    protected $fillable = ['paciente_id','doctor_id','fecha','observaciones'];
    public function paciente() { return $this->belongsTo(OdontoPaciente::class, 'paciente_id'); }
    public function doctor() { return $this->belongsTo(OdontoDoctor::class, 'doctor_id'); }
    public function piezas() { return $this->hasMany(OdontoOdontogramaPieza::class, 'odontograma_id'); }
}
