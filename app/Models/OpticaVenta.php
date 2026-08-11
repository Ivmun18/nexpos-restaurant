<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OpticaVenta extends Model {
    protected $table = 'optica_ventas';
    protected $fillable = ['empresa_id','paciente_id','receta_id','caja_id','user_id','numero_venta','fecha','subtotal','igv','total','monto_pagado','vuelto','metodo_pago','tipo_comprobante','ruc_cliente','razon_social_cliente','serie_comprobante','numero_comprobante','estado','sunat_estado','sunat_respuesta'];
    protected $casts = ['fecha' => 'date'];
    public function paciente() { return $this->belongsTo(OpticaPaciente::class, 'paciente_id'); }
    public function items() { return $this->hasMany(OpticaVentaItem::class, 'venta_id'); }
    public function caja() { return $this->belongsTo(OpticaCaja::class, 'caja_id'); }
}
