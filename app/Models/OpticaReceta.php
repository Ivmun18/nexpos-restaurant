<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OpticaReceta extends Model {
    protected $table = 'optica_recetas';
    protected $fillable = ['empresa_id','paciente_id','ficha_id','numero_receta','fecha','tipo','indicaciones'];
    protected $casts = ['fecha' => 'date'];
    public function paciente() { return $this->belongsTo(OpticaPaciente::class, 'paciente_id'); }
    public function ficha() { return $this->belongsTo(OpticaFicha::class, 'ficha_id'); }
}
