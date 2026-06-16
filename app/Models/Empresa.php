<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($empresa) {
            if (empty($empresa->modules_enabled)) {
                $defaults = [
                    'restaurante'  => ['mesas','carta','cocina','comandas','turnos','pos_restaurante','mozos','reportes','facturacion','clientes','productos','proveedores','compras','caja','usuarios','insumos','recetas'],
                    'minimarket'   => ['productos','inventario','pos_minimarket','pos_retail','proveedores','caja','reportes','facturacion'],
                    'farmacia'     => ['productos','inventario','pos_farmacia','recetas','vencimientos','proveedores','caja','reportes','facturacion'],
                    'ferreteria'   => ['productos','inventario','pos_ferreteria','proveedores','caja','reportes','facturacion'],
                    'notaria'      => ['actos','expedientes','caja','comprobantes','clientes','usuarios','seguimiento'],
                    'odontologia'  => ['odontologia','citas','pacientes','recetas','caja','reportes','facturacion'],
                    'gimnasio'     => ['miembros','membresias','pagos','caja','reportes'],
                    'hotel'        => ['habitaciones','reservas','huespedes','caja','reportes','facturacion'],
                ];
                $empresa->modules_enabled = $defaults[$empresa->industry_type] ?? [];
            }
        });
    }

    use Auditable;

    protected $auditModulo = 'Empresas';
    protected $fillable = [
        'ruc',
        'razon_social',
        'nombre_comercial',
        'direccion',
        'ubigeo',
        'distrito',
        'provincia',
        'departamento',
        'telefono',
        'email',
        'industry_type',
        'qf_regente_nombre',
        'qf_regente_cqfp',
        'numero_digemid',
        'autorizacion_sanitaria',
        'modules_enabled',
        'nubefact_token',
        'nubefact_demo',
        'zona_exonerada',
        'regimen_tributario',
        'modalidad_cobro',
        'proveedor_facturacion',
        'apisunat_token',
        'apisunat_ruc',
        'apisunat_usuario_sol',
        'apisunat_clave_sol',
        'apisunat_demo',
        'serie_boleta',
        'serie_factura',
        'serie_nota_credito',
        'ultimo_num_boleta',
        'ultimo_num_factura',
        'ultimo_num_nota_credito',
    ];

    protected $casts = [
        'modules_enabled' => 'array',
        'nubefact_demo' => 'boolean',
        'apisunat_demo' => 'boolean',
        'zona_exonerada' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Métodos para numeración de comprobantes
    public function siguienteNumeroBoleta(): int
    {
        $this->increment('ultimo_num_boleta');
        return $this->ultimo_num_boleta;
    }

    public function siguienteNumeroFactura(): int
    {
        $this->increment('ultimo_num_factura');
        return $this->ultimo_num_factura;
    }

    public function siguienteNumeroNotaCredito(): int
    {
        $this->increment('ultimo_num_nota_credito');
        return $this->ultimo_num_nota_credito;
    }

    // Métodos para industria
    public function isRestaurante(): bool
    {
        return $this->industry_type === 'restaurante';
    }

    public function isMinimarket(): bool
    {
        return $this->industry_type === 'minimarket';
    }

    public function isFarmacia(): bool
    {
        return $this->industry_type === 'farmacia';
    }

    public function isFerreteria(): bool
    {
        return $this->industry_type === 'ferreteria';
    }

    public function hasModule(string $module): bool
    {
        return in_array($module, $this->modules_enabled ?? []);
    }

    // Módulos por defecto según industria
    public static function getDefaultModules(string $industry): array
    {
        return match($industry) {
            'restaurante' => [
                'mesas',
                'carta',
                'pos_restaurante',
                'comandas',
                'cocina',
                'mozos',
                'caja',
                'turnos',
                'reportes',
                'facturacion',
            ],
            'minimarket' => [
                'productos',
                'inventario',
                'pos_retail',
                'proveedores',
                'caja',
                'reportes',
                'facturacion',
            ],
            'farmacia' => [
                'productos',
                'inventario',
                'pos_retail',
                'proveedores',
                'lotes',
                'vencimientos',
                'caja',
                'reportes',
                'facturacion',
            ],
            'ferreteria' => [
                'productos',
                'inventario',
                'pos_retail',
                'proveedores',
                'cotizaciones',
                'caja',
                'reportes',
                'facturacion',
            ],
            default => [],
        };
    }
}
