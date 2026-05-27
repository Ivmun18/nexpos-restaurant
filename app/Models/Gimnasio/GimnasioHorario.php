<?php
namespace App\Models\Gimnasio;
use Illuminate\Database\Eloquent\Model;

class GimnasioHorario extends Model {
    protected $table = 'gimnasio_horarios';
    protected $fillable = ['empresa_id','clase_id','instructor_id','dia','hora_inicio','hora_fin','activo'];
    public function clase() { return $this->belongsTo(GimnasioClase::class, 'clase_id'); }
    public function instructor() { return $this->belongsTo(GimnasioInstructor::class, 'instructor_id'); }
    public function reservas() { return $this->hasMany(GimnasioReserva::class, 'horario_id'); }
}
