<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
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
