<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HotelTipoHabitacion extends Model {
    protected $table = 'hotel_tipos_habitacion';
    protected $fillable = ['empresa_id','nombre','descripcion','precio_noche','capacidad','comodidades','activo'];
    protected $casts = ['comodidades' => 'array', 'activo' => 'boolean'];
    public function habitaciones() { return $this->hasMany(HotelHabitacion::class, 'tipo_id'); }
}
