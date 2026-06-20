<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OpticaProducto extends Model {
    protected $table = 'optica_productos';
    protected $fillable = ['empresa_id','codigo','nombre','categoria','marca','precio_compra','precio_venta','stock','stock_minimo','unidad','activo'];
    protected $casts = ['activo' => 'boolean'];
    public function scopeActivo($q) { return $q->where('activo', true); }
    public function scopeStockBajo($q) { return $q->whereColumn('stock', '<=', 'stock_minimo'); }
}
