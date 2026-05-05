<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuCategoria;
use App\Models\MenuProducto;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Entradas',
                'icono'  => '🥗',
                'color'  => '#10B981',
                'orden'  => 1,
                'productos' => [
                    ['nombre' => 'Causa limeña',      'precio' => 18.00, 'tiempo_preparacion' => 10],
                    ['nombre' => 'Ceviche de entrada', 'precio' => 22.00, 'tiempo_preparacion' => 12],
                    ['nombre' => 'Tequeños (6 unid.)', 'precio' => 15.00, 'tiempo_preparacion' => 8],
                ],
            ],
            [
                'nombre' => 'Fondos',
                'icono'  => '🍽️',
                'color'  => '#F59E0B',
                'orden'  => 2,
                'productos' => [
                    ['nombre' => 'Lomo saltado',         'precio' => 35.00, 'tiempo_preparacion' => 15],
                    ['nombre' => 'Ají de gallina',       'precio' => 28.00, 'tiempo_preparacion' => 12],
                    ['nombre' => 'Pollo a la brasa 1/4', 'precio' => 22.00, 'tiempo_preparacion' => 20],
                ],
            ],
            [
                'nombre' => 'Bebidas',
                'icono'  => '🥤',
                'color'  => '#3B82F6',
                'orden'  => 3,
                'productos' => [
                    ['nombre' => 'Chicha morada',      'precio' => 8.00,  'tiempo_preparacion' => 2],
                    ['nombre' => 'Inca Kola personal', 'precio' => 5.00,  'tiempo_preparacion' => 1],
                    ['nombre' => 'Agua sin gas',       'precio' => 4.00,  'tiempo_preparacion' => 1],
                    ['nombre' => 'Maracuyá frozen',    'precio' => 12.00, 'tiempo_preparacion' => 5],
                ],
            ],
            [
                'nombre' => 'Postres',
                'icono'  => '🍮',
                'color'  => '#EC4899',
                'orden'  => 4,
                'productos' => [
                    ['nombre' => 'Suspiro limeño',   'precio' => 12.00, 'tiempo_preparacion' => 3],
                    ['nombre' => 'Mazamorra morada', 'precio' => 10.00, 'tiempo_preparacion' => 3],
                    ['nombre' => 'Tres leches',      'precio' => 14.00, 'tiempo_preparacion' => 3],
                ],
            ],
        ];

        foreach ($categorias as $catData) {
            $productos = $catData['productos'];
            unset($catData['productos']);

            $categoria = MenuCategoria::create($catData);

            foreach ($productos as $i => $prod) {
                MenuProducto::create([
                    'menu_categoria_id'  => $categoria->id,
                    'nombre'             => $prod['nombre'],
                    'precio'             => $prod['precio'],
                    'tiempo_preparacion' => $prod['tiempo_preparacion'],
                    'orden'              => $i + 1,
                    'disponible'         => true,
                    'activo'             => true,
                ]);
            }
        }
    }
}