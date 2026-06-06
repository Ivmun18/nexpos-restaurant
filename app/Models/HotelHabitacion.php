<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HotelHabitacion extends Model {
    protected $table = 'hotel_habitaciones';
    protected $fillable = ['empresa_id','tipo_id','numero','piso','estado','observaciones'];
    public function tipo() { return $this->belongsTo(HotelTipoHabitacion::class, 'tipo_id'); }
    public function reservas() { return $this->hasMany(HotelReserva::class, 'habitacion_id'); }
    public function reservaActual() { return $this->hasOne(HotelReserva::class, 'habitacion_id')->where('estado', 'checkin'); }
    public function reservasFuturas() { return $this->hasMany(HotelReserva::class, 'habitacion_id')->where('estado', 'reservado')->where('fecha_checkin', '>', now())->orderBy('fecha_checkin'); }
}
