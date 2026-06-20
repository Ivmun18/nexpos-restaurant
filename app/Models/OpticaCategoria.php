<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OpticaCategoria extends Model {
    protected $table = 'optica_categorias';
    protected $fillable = ['empresa_id','nombre','color','activo'];
    protected $casts = ['activo' => 'boolean'];
    public function productos() { return $this->hasMany(OpticaProducto::class, 'categoria_id'); }
}
