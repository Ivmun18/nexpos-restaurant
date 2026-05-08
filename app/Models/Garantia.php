<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Garantia extends Model
{
    protected $fillable = [
        'empresa_id', 'producto_id', 'cliente_id', 'numero',
        'cliente_nombre', 'cliente_telefono', 'producto_descripcion',
        'marca', 'modelo', 'serie', 'fecha_compra', 'fecha_vencimiento',
        'meses_garantia', 'estado', 'condiciones', 'observaciones',
    ];

    public function producto() { return $this->belongsTo(Producto::class); }
    public function cliente()  { return $this->belongsTo(Cliente::class); }
}
