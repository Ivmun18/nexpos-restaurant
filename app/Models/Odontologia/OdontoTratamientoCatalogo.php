<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoTratamientoCatalogo extends Model {
    protected $table = 'odonto_tratamientos_catalogo';
    protected $fillable = [
        'empresa_id','codigo','nombre','categoria',
        'descripcion','precio','precio_base','duracion_minutos','activo'
    ];
    protected $casts = ['activo' => 'boolean', 'precio' => 'float', 'precio_base' => 'float'];

    public function getPrecioAttribute($value) {
        return $value ?: $this->attributes['precio_base'] ?? 0;
    }
}
