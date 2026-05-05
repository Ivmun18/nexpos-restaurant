<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Helpers\EmpresaHelper;
use Illuminate\Http\Request;

class ConsultaController extends Controller
{
    public function consultarRuc(string $ruc)
    {
        if (strlen($ruc) !== 11 || !is_numeric($ruc)) {
            return response()->json(['error' => 'RUC invalido'], 400);
        }

        // Buscar en clientes registrados
        $cliente = Cliente::where('empresa_id', EmpresaHelper::id())
            ->where('numero_documento', $ruc)
            ->first();

        if ($cliente) {
            return response()->json([
                'ruc'          => $ruc,
                'razon_social' => $cliente->razon_social,
                'direccion'    => $cliente->direccion ?? '',
                'fuente'       => 'local',
            ]);
        }

        return response()->json(['error' => 'RUC no encontrado en clientes registrados. Ingresa los datos manualmente.'], 404);
    }

    public function consultarDni(string $dni)
    {
        if (strlen($dni) !== 8 || !is_numeric($dni)) {
            return response()->json(['error' => 'DNI invalido'], 400);
        }

        // Buscar en clientes registrados
        $cliente = Cliente::where('empresa_id', EmpresaHelper::id())
            ->where('numero_documento', $dni)
            ->first();

        if ($cliente) {
            return response()->json([
                'dni'            => $dni,
                'nombre_completo'=> $cliente->razon_social,
                'fuente'         => 'local',
            ]);
        }

        return response()->json(['error' => 'DNI no encontrado en clientes registrados.'], 404);
    }
}