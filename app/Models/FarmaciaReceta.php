<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmaciaReceta extends Model
{
    protected $table = 'farmacia_recetas';

    protected $fillable = [
        'empresa_id',
        'venta_id',
        'numero_receta',
        'fecha',
        'medico',
        'especialidad',
        'establecimiento',
        'paciente_nombre',
        'paciente_dni',
        'items',
        'estado',
        'observaciones',
        'usuario_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'items' => 'array',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
