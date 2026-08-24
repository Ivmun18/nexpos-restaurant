<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Seeder;

class SucursalSeeder extends Seeder
{
    public function run(): void
    {
        $local1 = Sucursal::create([
            'empresa_id' => 1,
            'nombre'     => 'Local 1',
            'activo'     => true,
        ]);

        Sucursal::create([
            'empresa_id' => 1,
            'nombre'     => 'Local 2',
            'activo'     => true,
        ]);

        User::whereIn('email', ['cocina@nexpos.com', 'cajero@nexpos.com', 'mozo@nexpos.com'])
            ->update(['sucursal_id' => $local1->id]);
    }
}
