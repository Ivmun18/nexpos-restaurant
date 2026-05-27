<?php

namespace App\Services\Facturacion;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class APISunatService implements FacturacionInterface
{
    private string $token;
    private string $ruc;
    private string $usuarioSol;
    private string $claveSol;
    private bool $demo;
    private string $baseUrl;

    public function __construct(string $token, string $ruc, string $usuarioSol, string $claveSol, bool $demo = false)
    {
        $this->token = $token;
        $this->ruc = $ruc;
        $this->usuarioSol = $usuarioSol;
        $this->claveSol = $claveSol;
        $this->demo = $demo;
        $this->baseUrl = $demo 
            ? 'https://api-demo.apisunat.pe/api/v1'
            : 'https://api.apisunat.pe/api/v1';
    }

    public function emitirBoleta(array $datos): array
    {
        $payload = [
            'operacion' => 'generar_comprobante',
            'tipo_de_comprobante' => '03',
            'serie' => $datos['serie'],
            'numero' => $datos['numero'],
            'cliente' => [
                'tipo_documento' => $datos['cliente_tipo_documento'],
                'numero_documento' => $datos['cliente_numero_documento'],
                'nombre' => $datos['cliente_nombre'],
                'direccion' => $datos['cliente_direccion'] ?? '',
                'email' => $datos['cliente_email'] ?? '',
            ],
            'fecha_emision' => $datos['fecha_emision'] ?? date('Y-m-d'),
            'moneda' => 'PEN',
            'totales' => [
                'gravada' => $datos['total_gravada'],
                'igv' => $datos['total_igv'],
                'total' => $datos['total'],
            ],
            'items' => $datos['items'],
        ];

        return $this->enviarComprobante($payload);
    }

    public function emitirFactura(array $datos): array
    {
        $payload = [
            'operacion' => 'generar_comprobante',
            'tipo_de_comprobante' => '01',
            'serie' => $datos['serie'],
            'numero' => $datos['numero'],
            'cliente' => [
                'tipo_documento' => '6',
                'numero_documento' => $datos['cliente_ruc'],
                'nombre' => $datos['cliente_razon_social'],
                'direccion' => $datos['cliente_direccion'],
                'email' => $datos['cliente_email'] ?? '',
            ],
            'fecha_emision' => $datos['fecha_emision'] ?? date('Y-m-d'),
            'moneda' => 'PEN',
            'totales' => [
                'gravada' => $datos['total_gravada'],
                'igv' => $datos['total_igv'],
                'total' => $datos['total'],
            ],
            'items' => $datos['items'],
        ];

        return $this->enviarComprobante($payload);
    }

    public function emitirNotaCredito(array $datos): array
    {
        return ['success' => false, 'message' => 'No implementado'];
    }

    public function consultarEstado(string $serie, string $numero): array
    {
        return ['success' => false, 'message' => 'No implementado'];
    }

    public function probarConexion(): array
    {
        try {
            $response = Http::withToken($this->token)
                ->timeout(10)
                ->get($this->baseUrl . '/ping');

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Conexión exitosa con APISunat' : 'Error de conexión',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    private function enviarComprobante(array $payload): array
    {
        try {
            $response = Http::withToken($this->token)
                ->timeout(30)
                ->post($this->baseUrl . '/comprobantes', $payload);

            $data = $response->json();

            if ($response->successful() && isset($data['aceptado']) && $data['aceptado']) {
                return [
                    'success' => true,
                    'aceptada_por_sunat' => true,
                    'codigo_hash' => $data['hash'] ?? '',
                    'enlace_pdf' => $data['pdf_url'] ?? '',
                    'enlace_xml' => $data['xml_url'] ?? '',
                    'enlace_cdr' => $data['cdr_url'] ?? '',
                    'sunat_descripcion' => $data['descripcion'] ?? 'Aceptado',
                ];
            }

            return [
                'success' => false,
                'message' => $data['mensaje'] ?? 'Error desconocido',
                'aceptada_por_sunat' => false,
            ];

        } catch (\Exception $e) {
            Log::error('Error APISunat: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage(),
                'aceptada_por_sunat' => false,
            ];
        }
    }
}
