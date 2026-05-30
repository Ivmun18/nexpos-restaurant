<?php
namespace App\Models\Gimnasio;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class GimnasioPlan extends Model {
    use Auditable;

    protected $auditModulo = 'Gimnasio';
    protected $table = 'gimnasio_planes';
    protected $fillable = ['empresa_id','nombre','precio','duracion_dias','incluye_clases','incluye_pt','sesiones_pt','descripcion','activo'];
    public function miembros() { return $this->hasMany(GimnasioMiembro::class, 'plan_id'); }
}
