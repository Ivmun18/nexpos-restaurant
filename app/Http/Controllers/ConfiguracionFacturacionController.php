<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class ConfiguracionFacturacionController extends Controller
{
    public function index()
    {
        $empresa = \App\Models\Empresa::where('id', auth()->user()->empresa_id)->first();
        return Inertia::render('Configuracion/Facturacion', [
            'empresa' => $empresa,
        ]);
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'regimen_tributario'   => 'required|in:GENERAL,MYPE,RER,RUS',
            'serie_boleta'         => 'required|max:4',
            'serie_factura'        => 'nullable|max:4',
            'apisunat_token'       => 'nullable|string',
            'apisunat_usuario_sol' => 'nullable|string',
            'apisunat_clave_sol'   => 'nullable|string',
            'apisunat_demo'        => 'boolean',
        ]);

        \DB::table('empresas')
            ->where('id', auth()->user()->empresa_id)
            ->update([
                'regimen_tributario'    => $request->regimen_tributario,
                'serie_boleta'          => strtoupper($request->serie_boleta),
                'serie_factura'         => strtoupper($request->serie_factura ?? 'F001'),
                'proveedor_facturacion' => 'apisunat',
                'apisunat_token'        => $request->apisunat_token,
                'apisunat_usuario_sol'  => $request->apisunat_usuario_sol,
                'apisunat_clave_sol'    => $request->apisunat_clave_sol,
                'apisunat_demo'         => $request->boolean('apisunat_demo'),
            ]);

        return redirect()->back()->with('success', 'Configuracion guardada correctamente');
    }

    public function probar(Request $request)
    {
        $token = $request->token;
        $ruc   = auth()->user()->empresa->ruc;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get('https://api.apisunat.com/v1/personas/' . $ruc);

            if ($response->successful() || $response->status() === 404) {
                return response()->json(['ok' => true, 'mensaje' => 'Conexion exitosa con APISUNAT']);
            }

            return response()->json(['ok' => false, 'mensaje' => 'Token invalido'], 422);

        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
