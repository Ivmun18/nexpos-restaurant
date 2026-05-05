<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'compras';

    protected $fillable = [
        'empresa_id',
        'proveedor_id',
        'usuario_id',
        'tipo_comprobante',
        'serie_proveedor',
        'correlativo_proveedor',
        'numero_comprobante',
        'fecha_emision',
        'fecha_vencimiento',
        'moneda',
        'tipo_cambio',
        'total_gravado',
        'total_exonerado',
        'total_igv',
        'total',
        'forma_pago',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_emision'    => 'date',
        'fecha_vencimiento'=> 'date',
        'total'            => 'decimal:2',
        'total_igv'        => 'decimal:2',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalle()
    {
        return $this->hasMany(CompraDetalle::class);
    }
}