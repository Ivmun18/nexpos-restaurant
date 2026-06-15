<?php
namespace App\Models\Odontologia;

use Illuminate\Database\Eloquent\Model;

class OdontoReceta extends Model
{
    protected $table = 'odonto_recetas';
    protected $fillable = ['paciente_id','doctor_id','cita_id','fecha','indicaciones'];

    public function items() {
        return $this->hasMany(OdontoRecetaItem::class, 'receta_id');
    }
    public function doctor() {
        return $this->belongsTo(OdontoDoctor::class, 'doctor_id');
    }
}
