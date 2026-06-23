<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoRadiografia extends Model {
    protected $table = 'odonto_radiografias';
    protected $fillable = ['paciente_id','doctor_id','fecha','tipo','archivo_url','descripcion'];
}
