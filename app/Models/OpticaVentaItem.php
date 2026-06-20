<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OpticaVentaItem extends Model {
    protected $table = 'optica_venta_items';
    protected $fillable = ['venta_id','producto_id','descripcion','cantidad','precio_unitario','subtotal'];
    public function producto() { return $this->belongsTo(OpticaProducto::class, 'producto_id'); }
    public function venta() { return $this->belongsTo(OpticaVenta::class, 'venta_id'); }
}
