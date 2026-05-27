<?php

namespace App\Services\Facturacion;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NubefactService implements FacturacionInterface
{
    private string $token;
    private string $ruc;
    private bool $demo;
    private string $baseUrl;

    public function __construct(string $token, string $ruc, bool $demo = false)
    {
        $this->token = $token;
        $this->ruc = $ruc;
        $this->demo = $demo;
        $this->baseUrl = $demo 
            ? 'https://demo-ose.nubefact.com/ol-ti-itcpe/billService'
            : 'https://ose.nubefact.com/ol-ti-itcpe/billService';
    }

    public function emitirBoleta(array $datos): array
    {
        $payload = [
            'operacion' => 'generar_comprobante',
            'tipo_de_comprobante' => 3, // Boleta
            'serie' => $datos['serie'],
            'numero' => $datos['numero'],
            'sunat_transaction' => 1,
            'cliente_tipo_de_documento' => $datos['cliente_tipo_documento'],
            'cliente_numero_de_documento' => $datos['cliente_numero_documento'],
            'cliente_denominacion' => $datos['cliente_nombre'],
            'cliente_direccion' => $datos['cliente_direccion'] ?? '',
            'cliente_email' => $datos['cliente_email'] ?? '',
            'fecha_de_emision' => $datos['fecha_emision'] ?? date('Y-m-d'),
            'moneda' => 1, // Soles
            'total_gravada' => $datos['total_gravada'],
            'total_igv' => $datos['total_igv'],
            'total' => $datos['total'],
            'items' => $datos['items'],
        ];

        return $this->enviarComprobante($payload);
    }

    public function emitirFactura(array $datos): array
    {
        $payload = [
            'operacion' => 'generar_comprobante',
            'tipo_de_comprobante' => 1, // Factura
            'serie' => $datos['serie'],
            'numero' => $datos['numero'],
            'sunat_transaction' => 1,
            'cliente_tipo_de_documento' => 6, // RUC
            'cliente_numero_de_documento' => $datos['cliente_ruc'],
            'cliente_denominacion' => $datos['cliente_razon_social'],
            'cliente_direccion' => $datos['cliente_direccion'],
            'cliente_email' => $datos['cliente_email'] ?? '',
            'fecha_de_emision' => $datos['fecha_emision'] ?? date('Y-m-d'),
            'moneda' => 1,
            'total_gravada' => $datos['total_gravada'],
            'total_igv' => $datos['total_igv'],
            'total' => $datos['total'],
            'items' => $datos['items'],
        ];

        return $this->enviarComprobante($payload);
    }

    public function emitirNotaCredito(array $datos): array
    {
        // Implementar según necesidad
        return ['success' => false, 'message' => 'No implementado'];
    }

    public function consultarEstado(string $serie, string $numero): array
    {
        // Implementar consulta a Nubefact
        return ['success' => false, 'message' => 'No implementado'];
    }

    public function probarConexion(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])->timeout(10)->get($this->baseUrl);

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Conexión exitosa con Nubefact' : 'Error de conexión',
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
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl, $payload);

            $data = $response->json();

            if (isset($data['aceptada_por_sunat']) && $data['aceptada_por_sunat']) {
                return [
                    'success' => true,
                    'aceptada_por_sunat' => true,
                    'codigo_hash' => $data['codigo_hash'] ?? '',
                    'enlace_pdf' => $data['enlace_del_pdf'] ?? '',
                    'enlace_xml' => $data['enlace_del_xml'] ?? '',
                    'enlace_cdr' => $data['enlace_del_cdr'] ?? '',
                    'sunat_descripcion' => $data['sunat_description'] ?? 'Aceptado',
                ];
            }

            return [
                'success' => false,
                'message' => $data['errors'] ?? 'Error desconocido',
                'aceptada_por_sunat' => false,
            ];

        } catch (\Exception $e) {
            Log::error('Error Nubefact: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage(),
                'aceptada_por_sunat' => false,
            ];
        }
    }
}
