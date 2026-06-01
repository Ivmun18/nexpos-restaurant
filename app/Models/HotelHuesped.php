<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HotelHuesped extends Model {
    protected $table = 'hotel_huespedes';
    protected $fillable = ['empresa_id','tipo_documento','numero_documento','nombre_completo','email','telefono','nacionalidad','procedencia'];
    public function reservas() { return $this->hasMany(HotelReserva::class, 'huesped_id'); }
}
