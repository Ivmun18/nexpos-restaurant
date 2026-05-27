<?php
namespace App\Models\Gimnasio;
use Illuminate\Database\Eloquent\Model;

class GimnasioSesionPt extends Model {
    protected $table = 'gimnasio_sesiones_pt';
    protected $fillable = ['empresa_id','miembro_id','instructor_id','fecha','hora_inicio','duracion_min','precio','incluida_en_plan','estado','observaciones'];
    protected $casts = ['fecha'=>'date'];
    public function miembro() { return $this->belongsTo(GimnasioMiembro::class, 'miembro_id'); }
    public function instructor() { return $this->belongsTo(GimnasioInstructor::class, 'instructor_id'); }
}
