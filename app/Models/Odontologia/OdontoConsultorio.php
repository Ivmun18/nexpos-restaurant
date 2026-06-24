<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoConsultorio extends Model {
    protected $table = 'odonto_consultorios';
    protected $fillable = ['empresa_id','nombre','color','orden','activo'];
    protected $casts = ['activo' => 'boolean'];
}
