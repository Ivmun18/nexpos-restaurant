<?php
namespace App\Models\Gimnasio;
use Illuminate\Database\Eloquent\Model;

class GimnasioPlan extends Model {
    protected $table = 'gimnasio_planes';
    protected $fillable = ['empresa_id','nombre','precio','duracion_dias','incluye_clases','incluye_pt','sesiones_pt','descripcion','activo'];
    public function miembros() { return $this->hasMany(GimnasioMiembro::class, 'plan_id'); }
}
