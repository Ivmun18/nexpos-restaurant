<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelCargo extends Model
{
    protected $table = 'hotel_cargos';
    protected $fillable = [
        'empresa_id', 'reserva_id', 'producto_id', 'usuario_id',
        'descripcion', 'cantidad', 'precio_unitario', 'subtotal'
    ];
    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    public function producto() { return $this->belongsTo(HotelProducto::class, 'producto_id'); }
    public function reserva()  { return $this->belongsTo(HotelReserva::class, 'reserva_id'); }
    public function usuario()  { return $this->belongsTo(User::class, 'usuario_id'); }
}
