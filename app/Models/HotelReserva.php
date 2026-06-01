<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HotelReserva extends Model {
    protected $table = 'hotel_reservas';
    protected $fillable = ['empresa_id','habitacion_id','huesped_id','usuario_id','codigo','fecha_checkin','fecha_checkout_previsto','fecha_checkout_real','num_huespedes','precio_noche','num_noches','total','monto_pagado','estado','estado_pago','metodo_pago','observaciones'];
    protected $casts = ['fecha_checkin' => 'datetime', 'fecha_checkout_previsto' => 'datetime', 'fecha_checkout_real' => 'datetime'];
    public function habitacion() { return $this->belongsTo(HotelHabitacion::class, 'habitacion_id'); }
    public function huesped() { return $this->belongsTo(HotelHuesped::class, 'huesped_id'); }
    public function usuario() { return $this->belongsTo(User::class, 'usuario_id'); }
    public function pagos() { return $this->hasMany(HotelPago::class, 'reserva_id'); }
}
