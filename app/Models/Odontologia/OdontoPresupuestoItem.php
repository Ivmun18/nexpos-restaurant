<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoPresupuestoItem extends Model {
    protected $table = 'odonto_presupuesto_items';
    protected $fillable = ['presupuesto_id','tratamiento_id','numero_pieza','descripcion','precio','cantidad','subtotal','estado_item'];
    public function tratamiento() { return $this->belongsTo(OdontoTratamientoCatalogo::class, 'tratamiento_id'); }
}
