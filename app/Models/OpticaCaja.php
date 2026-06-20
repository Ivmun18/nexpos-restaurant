<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OpticaCaja extends Model {
    protected $table = 'optica_caja';
    protected $fillable = ['empresa_id','user_id','fecha','monto_inicial','monto_final','total_ventas','total_egresos','estado','abierta_en','cerrada_en','observaciones'];
    protected $casts = ['fecha' => 'date', 'abierta_en' => 'datetime', 'cerrada_en' => 'datetime'];
    public function movimientos() { return $this->hasMany(OpticaCajaMovimiento::class, 'caja_id'); }
    public function ventas() { return $this->hasMany(OpticaVenta::class, 'caja_id'); }
    public function scopeAbierta($q) { return $q->where('estado', 'abierta'); }
}
