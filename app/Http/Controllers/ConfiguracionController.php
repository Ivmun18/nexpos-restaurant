<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $empresa = auth()->user()->empresa;

        return Inertia::render('Configuracion/Index', [
            'empresa' => $empresa,
            'es_superadmin' => auth()->user()->esSuperAdmin(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'ruc'          => 'required|digits:11',
            'razon_social' => 'required|max:200',
            'direccion'    => 'nullable|max:300',
            'telefono'     => 'nullable|max:20',
            'email'        => 'nullable|email|max:150',
        ]);

        $empresa = auth()->user()->empresa;

        $datos = [
            'ruc'                => $request->ruc,
            'razon_social'       => strtoupper($request->razon_social),
            'nombre_comercial'   => $request->nombre_comercial,
            'direccion'          => $request->direccion,
            'ubigeo'             => $request->ubigeo,
            'distrito'           => $request->distrito,
            'provincia'          => $request->provincia,
            'departamento'       => $request->departamento,
            'telefono'           => $request->telefono,
            'email'              => $request->email,
            'web'                => $request->web,
            'regimen_tributario' => $request->regimen_tributario ?? 1,
            'buen_contribuyente' => $request->buen_contribuyente ?? false,
            'agente_retencion'   => $request->agente_retencion ?? false,
            'ambiente'           => $request->ambiente ?? 'beta',
            'zona_exonerada'     => $request->zona_exonerada ?? false,
        ];

        // Credenciales SUNAT
        if ($request->sol_usuario) {
            $datos['sol_usuario'] = $request->sol_usuario;
        }
        if ($request->sol_clave) {
            $datos['sol_clave'] = $request->sol_clave;
        }

        // Logo
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $datos['logo_path'] = $path;
        }

        // Certificado digital
        if ($request->hasFile('certificado')) {
            $path = $request->file('certificado')->storeAs(
                'sunat/certificados',
                'certificado.pfx',
                'local'
            );
            $datos['cert_path'] = $path;

            // Convertir PFX a PEM automáticamente
            if ($request->cert_password) {
                $datos['cert_password'] = $request->cert_password;
                $this->convertirCertificado($request->cert_password);
            }
        }

        $empresa->update($datos);

        // Actualizar .env con nuevas credenciales SUNAT
        if ($request->sol_usuario || $request->ruc) {
            $this->actualizarEnv([
                'SUNAT_RUC'          => $request->ruc,
                'SUNAT_SOL_USUARIO'  => $request->sol_usuario ?? $empresa->sol_usuario,
                'SUNAT_AMBIENTE'     => $request->ambiente ?? 'beta',
            ]);
        }

        return back()->with('success', 'Configuración guardada correctamente.');
    }

    private function convertirCertificado(string $password): void
    {
        $pfxPath = storage_path('app/private/sunat/certificados/certificado.pfx');
        $pemPath = storage_path('app/private/sunat/certificados/certificado.pem');

        if (file_exists($pfxPath)) {
            $cmd = "openssl pkcs12 -in {$pfxPath} -out {$pemPath} -nodes -password pass:{$password} 2>/dev/null";
            exec($cmd);
        }
    }

    private function actualizarEnv(array $datos): void
    {
        $envPath = base_path('.env');
        $contenido = file_get_contents($envPath);

        foreach ($datos as $clave => $valor) {
            if (strpos($contenido, $clave . '=') !== false) {
                $contenido = preg_replace(
                    "/^{$clave}=.*/m",
                    "{$clave}={$valor}",
                    $contenido
                );
            } else {
                $contenido .= "\n{$clave}={$valor}";
            }
        }

        file_put_contents($envPath, $contenido);
    }

    public function updateNubefact(Request $request)
    {
        $request->validate([
            'nubefact_token' => 'required|string',
            'nubefact_demo'  => 'boolean',
            'serie_boleta'   => 'required|string|max:4',
            'serie_factura'  => 'required|string|max:4',
        ]);

        $empresa = auth()->user()->empresa;

        $empresa->update([
            'nubefact_token' => $request->nubefact_token,
            'nubefact_demo'  => $request->nubefact_demo ?? true,
            'zona_exonerada'  => $request->zona_exonerada ?? false,
            'serie_boleta'   => strtoupper($request->serie_boleta),
            'serie_factura'  => strtoupper($request->serie_factura),
        ]);

        return back()->with('success', 'Configuración Nubefact guardada correctamente.');
    }

    public function testNubefact()
    {
        $empresa = auth()->user()->empresa;

        if (!$empresa->nubefact_token) {
            return response()->json(['error' => 'No hay token configurado'], 400);
        }

        $url = $empresa->nubefact_demo
            ? 'https://demo-api.nubefact.com/api/v1/'
            : 'https://api.nubefact.com/api/v1/';

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Token token=' . $empresa->nubefact_token,
                'Content-Type'  => 'application/json',
            ])->get($url);

            if ($response->successful()) {
                return response()->json(['success' => true, 'mensaje' => 'Conexión exitosa con Nubefact ✅']);
            } else {
                return response()->json(['error' => 'Token inválido o error de conexión'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
