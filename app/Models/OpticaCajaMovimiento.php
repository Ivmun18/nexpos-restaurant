<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OpticaCajaMovimiento extends Model {
    protected $table = 'optica_caja_movimientos';
    protected $fillable = ['caja_id','empresa_id','tipo','concepto','monto','referencia'];
    public function caja() { return $this->belongsTo(OpticaCaja::class, 'caja_id'); }
}
