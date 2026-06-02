<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoProveedor extends Model {
    protected $table = 'odonto_proveedores';
    protected $fillable = ['empresa_id','nombre','ruc','contacto','telefono','email','tipo','observaciones','activo'];
}
