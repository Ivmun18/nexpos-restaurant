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
            'regimen_tributario' => 'required|in:GENERAL,MYPE,RER,RUS',
            'serie_boleta'       => 'required|max:4',
            'serie_factura'      => 'nullable|max:4',
            'nubefact_token'     => 'nullable|string',
            'nubefact_demo'      => 'boolean',
        ]);

        \DB::table('empresas')
            ->where('id', auth()->user()->empresa_id)
            ->update([
                'regimen_tributario' => $request->regimen_tributario,
                'serie_boleta'       => strtoupper($request->serie_boleta),
                'serie_factura'      => strtoupper($request->serie_factura ?? 'F001'),
                'nubefact_token'     => $request->nubefact_token,
                'proveedor_facturacion' => $request->proveedor ?? 'apisunat',
                'nubefact_demo'      => $request->nubefact_demo ?? false,
            ]);

        return redirect()->back()->with('success', 'Configuración guardada correctamente');
    }

    public function probar(Request $request)
    {
        $token    = $request->token;
        $proveedor = $request->proveedor ?? 'apisunat';

        try {
            if ($proveedor === 'apisunat') {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])->get('https://api.apisunat.com/v1/personas/' . auth()->user()->empresa->ruc);
            } else {
                $response = Http::withHeaders([
                    'Authorization' => 'Token token=' . $token,
                ])->get('https://api.nubefact.com/api/v1/' . auth()->user()->empresa->ruc);
            }

            if ($response->successful() || $response->status() === 404) {
                return response()->json(['ok' => true, 'proveedor' => strtoupper($proveedor)]);
            }

            return response()->json(['ok' => false], 422);

        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
