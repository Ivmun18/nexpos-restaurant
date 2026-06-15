<?php
namespace App\Models\Odontologia;

use Illuminate\Database\Eloquent\Model;

class OdontoRecetaItem extends Model
{
    protected $table = 'odonto_receta_items';
    protected $fillable = ['receta_id','medicamento','dosis','frecuencia','duracion','indicaciones'];

    public function receta() {
        return $this->belongsTo(OdontoReceta::class, 'receta_id');
    }
}
