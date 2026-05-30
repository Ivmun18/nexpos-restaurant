<?php
namespace App\Models\Gimnasio;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class GimnasioClase extends Model {
    use Auditable;

    protected $auditModulo = 'Gimnasio';
    protected $table = 'gimnasio_clases';
    protected $fillable = ['empresa_id','nombre','tipo','capacidad_max','duracion_min','descripcion','activo'];
    public function horarios() { return $this->hasMany(GimnasioHorario::class, 'clase_id'); }
}
