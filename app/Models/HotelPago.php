<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HotelPago extends Model {
    protected $table = 'hotel_pagos';
    protected $fillable = ['reserva_id','usuario_id','monto','metodo_pago','referencia','observaciones'];
    public function reserva() { return $this->belongsTo(HotelReserva::class, 'reserva_id'); }
}
