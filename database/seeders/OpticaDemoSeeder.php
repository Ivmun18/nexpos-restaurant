<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class OpticaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $empresa_id = 21;
        $now = Carbon::now();

        // 1. EMPRESA
        DB::table('empresas')->insertOrIgnore([
            'id'               => $empresa_id,
            'razon_social'     => 'Óptica VisionPlus Demo',
            'nombre_comercial' => 'VisionPlus',
            'ruc'              => '10000000021',
            'direccion'        => 'Jr. 2 de Mayo 345, Huánuco',
            'telefono'         => '062-512345',
            'email'            => 'demo@optica.com',
            'industry_type'    => 'optica',
            'activo'           => true,
            'regimen_tributario'=> 'RER',
            'serie_boleta'     => 'B001',
            'serie_factura'    => 'F001',
            'ultimo_num_boleta'=> 0,
            'ultimo_num_factura'=> 0,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);

        // 2. USUARIO ADMIN
        $exists = DB::table('users')->where('email', 'admin@optica.demo')->first();
        $userId = $exists ? $exists->id : DB::table('users')->insertGetId([
            'name'       => 'Admin Óptica Demo',
            'email'      => 'admin@optica.demo',
            'password'   => Hash::make('password'),
            'empresa_id'=> $empresa_id,
            'rol'        => 'admin_optica',
            'created_at'=> $now,
            'updated_at'=> $now,
        ]);

        // 3. PRODUCTOS
        $productos = [
            ['codigo'=>'LUN-001','nombre'=>'Luna Monofocal Blanca','categoria'=>'luna','marca'=>'Hoya','precio_compra'=>25,'precio_venta'=>60,'stock'=>50],
            ['codigo'=>'LUN-002','nombre'=>'Luna Progresiva','categoria'=>'luna','marca'=>'Essilor','precio_compra'=>80,'precio_venta'=>180,'stock'=>20],
            ['codigo'=>'LUN-003','nombre'=>'Luna Fotocromática','categoria'=>'luna','marca'=>'Transitions','precio_compra'=>60,'precio_venta'=>140,'stock'=>15],
            ['codigo'=>'LUN-004','nombre'=>'Luna Antirreflejo','categoria'=>'luna','marca'=>'Hoya','precio_compra'=>35,'precio_venta'=>80,'stock'=>30],
            ['codigo'=>'MON-001','nombre'=>'Montura Acetato Negra','categoria'=>'montura','marca'=>'Ray-Ban','precio_compra'=>40,'precio_venta'=>120,'stock'=>10],
            ['codigo'=>'MON-002','nombre'=>'Montura Metal Dorada','categoria'=>'montura','marca'=>'Silhouette','precio_compra'=>35,'precio_venta'=>100,'stock'=>8],
            ['codigo'=>'MON-003','nombre'=>'Montura Deportiva','categoria'=>'montura','marca'=>'Oakley','precio_compra'=>50,'precio_venta'=>150,'stock'=>5],
            ['codigo'=>'MON-004','nombre'=>'Montura Niños Flexible','categoria'=>'montura','marca'=>'Miraflex','precio_compra'=>20,'precio_venta'=>65,'stock'=>12],
            ['codigo'=>'LC-001','nombre'=>'Lente Contacto Diario','categoria'=>'lente_contacto','marca'=>'Acuvue','precio_compra'=>15,'precio_venta'=>35,'stock'=>40],
            ['codigo'=>'LC-002','nombre'=>'Lente Contacto Mensual','categoria'=>'lente_contacto','marca'=>'Biofinity','precio_compra'=>25,'precio_venta'=>55,'stock'=>25],
            ['codigo'=>'SOL-001','nombre'=>'Solución Multipropósito 360ml','categoria'=>'solucion','marca'=>'ReNu','precio_compra'=>12,'precio_venta'=>28,'stock'=>30],
            ['codigo'=>'ACC-001','nombre'=>'Estuche Rígido','categoria'=>'accesorio','marca'=>'Genérico','precio_compra'=>3,'precio_venta'=>10,'stock'=>50],
            ['codigo'=>'ACC-002','nombre'=>'Paño Microfibra','categoria'=>'accesorio','marca'=>'Genérico','precio_compra'=>1,'precio_venta'=>5,'stock'=>100],
            ['codigo'=>'ACC-003','nombre'=>'Spray Limpiador Lentes','categoria'=>'accesorio','marca'=>'Zeiss','precio_compra'=>5,'precio_venta'=>15,'stock'=>20],
        ];

        $productoIds = [];
        foreach ($productos as $p) {
            $productoIds[] = DB::table('optica_productos')->insertGetId(array_merge($p, [
                'empresa_id'   => $empresa_id,
                'stock_minimo'=> 3,
                'unidad'      => 'und',
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]));
        }

        // 4. PACIENTES
        $pacientes = [
            ['nombre'=>'Carlos','apellidos'=>'Ríos Tuesta','dni'=>'45123678','telefono'=>'987654321','sexo'=>'M','fecha_nacimiento'=>'1985-03-12'],
            ['nombre'=>'María','apellidos'=>'Flores Vásquez','dni'=>'52341890','telefono'=>'976543210','sexo'=>'F','fecha_nacimiento'=>'1992-07-25'],
            ['nombre'=>'Luis','apellidos'=>'Gonzales Prado','dni'=>'38901234','telefono'=>'965432109','sexo'=>'M','fecha_nacimiento'=>'1978-11-08'],
            ['nombre'=>'Ana','apellidos'=>'Torres Sánchez','dni'=>'61234567','telefono'=>'954321098','sexo'=>'F','fecha_nacimiento'=>'2001-01-30'],
            ['nombre'=>'Pedro','apellidos'=>'Huanca López','dni'=>'29876543','telefono'=>'943210987','sexo'=>'M','fecha_nacimiento'=>'1968-06-15'],
        ];

        $pacienteIds = [];
        foreach ($pacientes as $p) {
            $pacienteIds[] = DB::table('optica_pacientes')->insertGetId(array_merge($p, [
                'empresa_id' => $empresa_id,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // 5. FICHAS OFTALMOLÓGICAS
        $fichas = [
            ['paciente_id'=>$pacienteIds[0],'fecha'=>'2026-06-01','od_esfera'=>-2.50,'od_cilindro'=>-0.75,'od_eje'=>180,'od_av'=>'20/20','oi_esfera'=>-2.25,'oi_cilindro'=>-0.50,'oi_eje'=>175,'oi_av'=>'20/20','div'=>64],
            ['paciente_id'=>$pacienteIds[1],'fecha'=>'2026-06-05','od_esfera'=>-1.00,'od_cilindro'=>0,'od_eje'=>0,'od_av'=>'20/30','oi_esfera'=>-1.25,'oi_cilindro'=>-0.25,'oi_eje'=>90,'oi_av'=>'20/25','div'=>62],
            ['paciente_id'=>$pacienteIds[2],'fecha'=>'2026-06-10','od_esfera'=>1.50,'od_cilindro'=>0,'od_eje'=>0,'od_av'=>'20/40','oi_esfera'=>1.75,'oi_cilindro'=>-0.50,'oi_eje'=>160,'oi_av'=>'20/40','div'=>66,'od_adicion'=>2.00,'oi_adicion'=>2.00],
            ['paciente_id'=>$pacienteIds[3],'fecha'=>'2026-06-12','od_esfera'=>-3.00,'od_cilindro'=>-1.00,'od_eje'=>10,'od_av'=>'20/20','oi_esfera'=>-2.75,'oi_cilindro'=>-0.75,'oi_eje'=>170,'oi_av'=>'20/20','div'=>60],
            ['paciente_id'=>$pacienteIds[4],'fecha'=>'2026-06-15','od_esfera'=>2.00,'od_cilindro'=>0,'od_eje'=>0,'od_av'=>'20/50','oi_esfera'=>2.25,'oi_cilindro'=>0,'oi_eje'=>0,'oi_av'=>'20/50','div'=>68,'od_adicion'=>2.50,'oi_adicion'=>2.50],
        ];

        $fichaIds = [];
        foreach ($fichas as $f) {
            $fichaIds[] = DB::table('optica_fichas')->insertGetId(array_merge($f, [
                'empresa_id' => $empresa_id,
                'user_id'    => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // 6. RECETAS
        foreach ($fichaIds as $i => $fichaId) {
            DB::table('optica_recetas')->insert([
                'empresa_id'    => $empresa_id,
                'paciente_id'   => $pacienteIds[$i],
                'ficha_id'      => $fichaId,
                'numero_receta' => 'REC-' . str_pad($i+1, 4, '0', STR_PAD_LEFT),
                'fecha'         => $fichas[$i]['fecha'],
                'tipo'          => isset($fichas[$i]['od_adicion']) ? 'progresivo' : 'lejos',
                'indicaciones'  => 'Uso permanente. Control en 6 meses.',
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        // 7. CAJA
        $cajaId = DB::table('optica_caja')->insertGetId([
            'empresa_id'    => $empresa_id,
            'user_id'       => $userId,
            'fecha'         => '2026-06-19',
            'monto_inicial' => 200.00,
            'estado'        => 'abierta',
            'abierta_en'    => $now,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        // 8. VENTAS demo
        $ventas = [
            ['paciente_id'=>$pacienteIds[0],'total'=>240.00,'items'=>[['prod'=>0,'cant'=>2,'precio'=>60],['prod'=>4,'cant'=>1,'precio'=>120]]],
            ['paciente_id'=>$pacienteIds[1],'total'=>215.00,'items'=>[['prod'=>1,'cant'=>1,'precio'=>180],['prod'=>11,'cant'=>1,'precio'=>10],['prod'=>12,'cant'=>5,'precio'=>5]]],
            ['paciente_id'=>$pacienteIds[3],'total'=>150.00,'items'=>[['prod'=>5,'cant'=>1,'precio'=>100],['prod'=>3,'cant'=>1,'precio'=>80]]],
        ];

        foreach ($ventas as $i => $v) {
            $ventaId = DB::table('optica_ventas')->insertGetId([
                'empresa_id'       => $empresa_id,
                'paciente_id'      => $v['paciente_id'],
                'caja_id'          => $cajaId,
                'user_id'          => $userId,
                'numero_venta'     => 'VTA-' . str_pad($i+1, 6, '0', STR_PAD_LEFT),
                'fecha'            => '2026-06-19',
                'subtotal'         => round($v['total'] / 1.18, 2),
                'igv'              => round($v['total'] - $v['total'] / 1.18, 2),
                'total'            => $v['total'],
                'monto_pagado'     => $v['total'],
                'vuelto'           => 0,
                'metodo_pago'      => 'efectivo',
                'tipo_comprobante' => 'boleta',
                'estado'           => 'pagado',
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

            foreach ($v['items'] as $item) {
                DB::table('optica_venta_items')->insert([
                    'venta_id'        => $ventaId,
                    'producto_id'     => $productoIds[$item['prod']],
                    'descripcion'     => $productos[$item['prod']]['nombre'],
                    'cantidad'        => $item['cant'],
                    'precio_unitario' => $item['precio'],
                    'subtotal'        => $item['cant'] * $item['precio'],
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
            }

            DB::table('optica_caja_movimientos')->insert([
                'caja_id'     => $cajaId,
                'empresa_id'  => $empresa_id,
                'tipo'        => 'ingreso',
                'concepto'    => 'Venta VTA-' . str_pad($i+1, 6, '0', STR_PAD_LEFT),
                'monto'       => $v['total'],
                'referencia'  => (string)$ventaId,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        echo "\n✓ Seeder Óptica Demo completado. empresa_id=21\n";
    }
}
