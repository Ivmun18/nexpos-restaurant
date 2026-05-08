<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenTrabajo extends Model
{
    protected $table = 'ordenes_trabajo';

    protected $fillable = [
        'empresa_id', 'usuario_id', 'cliente_id', 'numero',
        'cliente_nombre', 'cliente_telefono', 'titulo', 'descripcion',
        'diagnostico', 'trabajos_realizados', 'estado', 'prioridad',
        'fecha_ingreso', 'fecha_estimada', 'fecha_entrega',
        'costo_mano_obra', 'costo_materiales', 'total', 'observaciones',
    ];

    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function usuario() { return $this->belongsTo(User::class, 'usuario_id'); }
}
