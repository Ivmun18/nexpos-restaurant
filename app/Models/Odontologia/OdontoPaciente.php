<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoPaciente extends Model {
    protected $table = 'odonto_pacientes';
    protected $fillable = ['empresa_id','nombres','apellidos','dni','fecha_nacimiento','sexo','telefono','email','direccion','grupo_sanguineo','alergias','antecedentes','contacto_emergencia','telefono_emergencia','activo'];
    public function citas() { return $this->hasMany(OdontoCita::class, 'paciente_id'); }
    public function historias() { return $this->hasMany(OdontoHistoriaClinica::class, 'paciente_id'); }
    public function odontogramas() { return $this->hasMany(OdontoOdontograma::class, 'paciente_id'); }
    public function presupuestos() { return $this->hasMany(OdontoPresupuesto::class, 'paciente_id'); }
    public function pagos() { return $this->hasMany(OdontoPago::class, 'paciente_id'); }
    public function recetas() { return $this->hasMany(\App\Models\Odontologia\OdontoReceta::class, 'paciente_id'); }
    public function radiografias() { return $this->hasMany(\App\Models\Odontologia\OdontoRadiografia::class, 'paciente_id'); }
    public function getNombreCompletoAttribute() { return $this->nombres . ' ' . $this->apellidos; }
}
