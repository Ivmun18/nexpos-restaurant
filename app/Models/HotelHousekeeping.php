<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HotelHousekeeping extends Model {
    protected $table = 'hotel_housekeeping';
    protected $fillable = ['empresa_id','habitacion_id','usuario_id','estado','observaciones','completado_at'];
    protected $casts = ['completado_at' => 'datetime'];
    public function habitacion() { return $this->belongsTo(HotelHabitacion::class, 'habitacion_id'); }
    public function usuario() { return $this->belongsTo(User::class, 'usuario_id'); }
}
