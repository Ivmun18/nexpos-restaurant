<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoOdontograma extends Model {
    protected $table = 'odonto_odontograma';
    protected $fillable = ['empresa_id','paciente_id','diente','estado','notas'];
}
