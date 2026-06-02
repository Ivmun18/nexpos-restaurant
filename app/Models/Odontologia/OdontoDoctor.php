<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoDoctor extends Model {
    protected $table = 'odonto_doctores';
    protected $fillable = ['empresa_id','nombre','especialidad','colegiatura','telefono','email','foto','activo'];
    public function horarios() { return $this->hasMany(OdontoHorarioDoctor::class, 'doctor_id'); }
    public function citas() { return $this->hasMany(OdontoCita::class, 'doctor_id'); }
}
