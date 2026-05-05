<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        // 1. RESTAURANTE
        $empresaRest = Empresa::create([
            'ruc' => '20600111222',
            'razon_social' => 'RESTAURANTE EL SABOR SAC',
            'nombre_comercial' => 'El Sabor',
            'direccion' => 'Av. Los Gourmet 456',
            'ubigeo' => '100101',
            'distrito' => 'HUANUCO',
            'provincia' => 'HUANUCO',
            'departamento' => 'HUANUCO',
            'industry_type' => 'restaurante',
            'modules_enabled' => ['mesas', 'carta', 'pos_restaurante', 'comandas', 'cocina', 'mozos', 'caja', 'turnos', 'reportes', 'facturacion'],
            'nubefact_token' => 'cff73d2f211d4b35b2bfa648bcb2648a2bc2cb0c3569433f8a825eb4d4d9cba2',
            'nubefact_demo' => true,
            'serie_boleta' => 'B001',
            'serie_factura' => 'F001',
            'ultimo_num_boleta' => 0,
            'ultimo_num_factura' => 0,
        ]);

        User::create([
            'name' => 'Demo Restaurante',
            'email' => 'demo@restaurante.com',
            'password' => Hash::make('demo123'),
            'empresa_id' => $empresaRest->id,
        ]);

        // 2. FARMACIA
        $empresaFarm = Empresa::create([
            'ruc' => '20600222333',
            'razon_social' => 'FARMACIA SALUD PLUS SAC',
            'nombre_comercial' => 'Salud+',
            'direccion' => 'Av. La Salud 789',
            'ubigeo' => '100101',
            'distrito' => 'HUANUCO',
            'provincia' => 'HUANUCO',
            'departamento' => 'HUANUCO',
            'industry_type' => 'farmacia',
            'modules_enabled' => ['productos', 'inventario', 'pos_retail', 'proveedores', 'lotes', 'vencimientos', 'caja', 'reportes', 'facturacion'],
            'nubefact_token' => 'cff73d2f211d4b35b2bfa648bcb2648a2bc2cb0c3569433f8a825eb4d4d9cba2',
            'nubefact_demo' => true,
            'serie_boleta' => 'B001',
            'serie_factura' => 'F001',
            'ultimo_num_boleta' => 0,
            'ultimo_num_factura' => 0,
        ]);

        User::create([
            'name' => 'Demo Farmacia',
            'email' => 'demo@farmacia.com',
            'password' => Hash::make('demo123'),
            'empresa_id' => $empresaFarm->id,
        ]);

        // 3. MINIMARKET
        $empresaMini = Empresa::create([
            'ruc' => '20600333444',
            'razon_social' => 'MINIMARKET EXPRESS SAC',
            'nombre_comercial' => 'Express',
            'direccion' => 'Jr. Comercio 321',
            'ubigeo' => '100101',
            'distrito' => 'HUANUCO',
            'provincia' => 'HUANUCO',
            'departamento' => 'HUANUCO',
            'industry_type' => 'minimarket',
            'modules_enabled' => ['productos', 'inventario', 'pos_retail', 'proveedores', 'caja', 'reportes', 'facturacion'],
            'nubefact_token' => 'cff73d2f211d4b35b2bfa648bcb2648a2bc2cb0c3569433f8a825eb4d4d9cba2',
            'nubefact_demo' => true,
            'serie_boleta' => 'B001',
            'serie_factura' => 'F001',
            'ultimo_num_boleta' => 0,
            'ultimo_num_factura' => 0,
        ]);

        User::create([
            'name' => 'Demo Minimarket',
            'email' => 'demo@minimarket.com',
            'password' => Hash::make('demo123'),
            'empresa_id' => $empresaMini->id,
        ]);

        echo "✅ 3 empresas y 3 usuarios demo creados\n";
    }
}
