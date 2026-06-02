<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoPagoCuota extends Model {
    protected $table = 'odonto_pago_cuotas';
    protected $fillable = ['pago_id','numero_cuota','monto','fecha_vencimiento','fecha_pago','metodo_pago','estado'];
    public function pago() { return $this->belongsTo(OdontoPago::class, 'pago_id'); }
}
