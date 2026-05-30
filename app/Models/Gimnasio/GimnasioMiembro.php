<?php
namespace App\Models\Gimnasio;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class GimnasioMiembro extends Model {
    use Auditable;

    protected $auditModulo = 'Gimnasio';
    protected $table = 'gimnasio_miembros';
    protected $fillable = ['empresa_id','nombre','apellidos','dni','telefono','email','fecha_nacimiento','sexo','foto','codigo_qr','plan_id','membrecia_inicio','membrecia_vencimiento','estado','observaciones','activo'];
    protected $casts = ['membrecia_inicio'=>'date','membrecia_vencimiento'=>'date','fecha_nacimiento'=>'date'];
    public function plan() { return $this->belongsTo(GimnasioPlan::class, 'plan_id'); }
    public function pagos() { return $this->hasMany(GimnasioPago::class, 'miembro_id'); }
    public function accesos() { return $this->hasMany(GimnasioAcceso::class, 'miembro_id'); }
    public function sesiones_pt() { return $this->hasMany(GimnasioSesionPt::class, 'miembro_id'); }
    public function rutinas() { return $this->hasMany(GimnasioRutina::class, 'miembro_id'); }
}
