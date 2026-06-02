<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoPedidoLaboratorio extends Model {
    protected $table = 'odonto_pedidos_laboratorio';
    protected $fillable = ['empresa_id','proveedor_id','paciente_id','doctor_id','fecha_pedido','fecha_entrega_esperada','fecha_entrega_real','tipo_trabajo','descripcion','color_protesis','costo','estado','observaciones'];
    public function proveedor() { return $this->belongsTo(OdontoProveedor::class, 'proveedor_id'); }
    public function paciente() { return $this->belongsTo(OdontoPaciente::class, 'paciente_id'); }
    public function doctor() { return $this->belongsTo(OdontoDoctor::class, 'doctor_id'); }
}
