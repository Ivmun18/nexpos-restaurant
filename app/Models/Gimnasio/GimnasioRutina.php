<?php
namespace App\Models\Gimnasio;
use Illuminate\Database\Eloquent\Model;

class GimnasioRutina extends Model {
    protected $table = 'gimnasio_rutinas';
    protected $fillable = ['empresa_id','miembro_id','instructor_id','nombre','ejercicios','fecha_inicio','fecha_fin','observaciones','activo'];
    protected $casts = ['fecha_inicio'=>'date','fecha_fin'=>'date','ejercicios'=>'array'];
    public function miembro() { return $this->belongsTo(GimnasioMiembro::class, 'miembro_id'); }
    public function instructor() { return $this->belongsTo(GimnasioInstructor::class, 'instructor_id'); }
}
