<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoInsumo extends Model {
    protected $table = 'odonto_insumos';
    protected $fillable = ['empresa_id','nombre','categoria','unidad','stock_actual','stock_minimo','precio_unitario','activo'];
}
