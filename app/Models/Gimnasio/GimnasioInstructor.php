<?php
namespace App\Models\Gimnasio;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class GimnasioInstructor extends Model {
    use Auditable;

    protected $auditModulo = 'Gimnasio';
    protected $table = 'gimnasio_instructores';
    protected $fillable = ['empresa_id','nombre','apellidos','dni','telefono','especialidad','comision_clase','comision_pt','activo'];
    public function horarios() { return $this->hasMany(GimnasioHorario::class, 'instructor_id'); }
    public function sesiones_pt() { return $this->hasMany(GimnasioSesionPt::class, 'instructor_id'); }
}
