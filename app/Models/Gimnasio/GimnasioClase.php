<?php
namespace App\Models\Gimnasio;
use Illuminate\Database\Eloquent\Model;

class GimnasioClase extends Model {
    protected $table = 'gimnasio_clases';
    protected $fillable = ['empresa_id','nombre','tipo','capacidad_max','duracion_min','descripcion','activo'];
    public function horarios() { return $this->hasMany(GimnasioHorario::class, 'clase_id'); }
}
