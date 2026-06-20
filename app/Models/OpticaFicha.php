<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OpticaFicha extends Model {
    protected $table = 'optica_fichas';
    protected $fillable = ['empresa_id','paciente_id','user_id','fecha','od_esfera','od_cilindro','od_eje','od_adicion','od_av','oi_esfera','oi_cilindro','oi_eje','oi_adicion','oi_av','div','observaciones'];
    protected $casts = ['fecha' => 'date'];
    public function paciente() { return $this->belongsTo(OpticaPaciente::class, 'paciente_id'); }
    public function recetas() { return $this->hasMany(OpticaReceta::class, 'ficha_id'); }
}
