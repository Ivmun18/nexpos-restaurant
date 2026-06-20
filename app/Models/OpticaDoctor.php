<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OpticaDoctor extends Model {
    protected $table = 'optica_doctores';
    protected $fillable = ['empresa_id','nombre','especialidad','colegiatura','telefono','email','activo'];
    protected $casts = ['activo' => 'boolean'];
    public function fichas() { return $this->hasMany(OpticaFicha::class, 'doctor_id'); }
}
