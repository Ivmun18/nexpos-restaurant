<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PagoProveedor extends Model
{
    use SoftDeletes;

    protected $table = 'pagos_proveedores';

    protected $fillable = [
        'empresa_id',
        'cuenta_por_pagar_id',
        'usuario_id',
        'monto',
        'fecha_pago',
        'numero_comprobante',
        'forma_pago',
        'referencia',
        'observaciones',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'date',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cuentaPorPagar()
    {
        return $this->belongsTo(CuentaPorPagar::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
