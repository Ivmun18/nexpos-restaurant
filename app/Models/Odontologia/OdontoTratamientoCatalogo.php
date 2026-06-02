<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoTratamientoCatalogo extends Model {
    protected $table = 'odonto_tratamientos_catalogo';
    protected $fillable = ['empresa_id','nombre','categoria','descripcion','precio_base','activo'];
}
