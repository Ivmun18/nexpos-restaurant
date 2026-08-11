<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoHorarioDoctor extends Model {
    protected $table = 'odonto_horarios_doctor';
    protected $fillable = ['doctor_id','dia_semana','hora_inicio','hora_fin','activo'];
}
