<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoGaleria extends Model {
    protected $table = 'odonto_galeria';
    protected $fillable = [
        'empresa_id','paciente_id','doctor_id','titulo',
        'tratamiento','foto_antes','foto_despues','descripcion','publica'
    ];
    protected $casts = ['publica' => 'boolean'];
}
