<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OpticaPaciente extends Model {
    protected $table = 'optica_pacientes';
    protected $fillable = ['empresa_id','nombre','apellidos','dni','telefono','email','fecha_nacimiento','sexo','direccion','observaciones'];
    public function fichas() { return $this->hasMany(OpticaFicha::class, 'paciente_id'); }
    public function recetas() { return $this->hasMany(OpticaReceta::class, 'paciente_id'); }
    public function ventas() { return $this->hasMany(OpticaVenta::class, 'paciente_id'); }
    public function getNombreCompletoAttribute() { return $this->nombre . ' ' . $this->apellidos; }
}
