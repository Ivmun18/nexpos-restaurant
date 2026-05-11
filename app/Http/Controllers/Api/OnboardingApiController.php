<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class OnboardingApiController extends Controller
{
    public function crearEmpresa(Request $request)
    {
        // Verificar clave secreta
        if ($request->header('X-Nexpos-Key') !== config('app.nexpos_secret_key')) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        DB::beginTransaction();
        try {
            // Definir módulos según industria
            $modulos = $this->modulosPorIndustria($request->industria);

            // Crear empresa
            $empresa = Empresa::create([
                'razon_social'         => $request->razon_social,
                'nombre_comercial'     => $request->razon_social,
                'ruc'                  => $request->ruc,
                'telefono'             => $request->telefono,
                'email'                => $request->email,
                'industry_type'        => $request->industria,
                'modules_enabled'      => $modulos,
                'regimen_tributario'   => 'GENERAL',
                'proveedor_facturacion'=> 'apisunat',
                'serie_boleta'         => 'B001',
                'serie_factura'        => 'F001',
                'activo'               => true,
            ]);

            // Crear usuario administrador
            $usuario = User::create([
                'name'       => $request->razon_social,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'empresa_id' => $empresa->id,
                'rol'        => 'admin',
            ]);

            DB::commit();

            return response()->json([
                'ok'         => true,
                'empresa_id' => $empresa->id,
                'usuario_id' => $usuario->id,
                'mensaje'    => 'Empresa creada correctamente',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function modulosPorIndustria(string $industria): array
    {
        return match($industria) {
            'restaurante'  => ['pos_restaurante', 'mesas', 'carta', 'cocina', 'caja'],
            'minimarket'   => ['pos_minimarket', 'caja'],
            'ferreteria'   => ['pos_ferreteria'],
            'farmacia'     => ['pos_minimarket', 'caja'],
            'panaderia'    => ['pos_minimarket', 'caja'],
            'veterinaria'  => ['pos_ferreteria'],
            'lavanderia'   => ['pos_ferreteria'],
            'peluqueria'   => ['pos_ferreteria'],
            'gym'          => ['pos_ferreteria'],
            'hotel'        => ['pos_restaurante'],
            'taller'       => ['pos_ferreteria'],
            'libreria'     => ['pos_minimarket'],
            'ropa'         => ['pos_ferreteria'],
            'joyeria'      => ['pos_ferreteria'],
            'consultorio'  => ['pos_ferreteria'],
            default        => ['pos_minimarket'],
        };
    }
}
