<?php

namespace App\Services;

use App\Models\Empresa;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NubefactService
{
    private Empresa $empresa;
    private string $baseUrl;

    public function __construct(Empresa $empresa)
    {
        $this->empresa = $empresa;
        
        // URL correcta de Nubefact con el UUID de la cuenta
        $this->baseUrl = 'https://api.nubefact.com/api/v1/9600a1af-9b17-4b08-97f7-8e26583d90a6';
    }

    public function emitirBoleta(array $data)
    {
        $numero = $this->empresa->siguienteNumeroBoleta();
        
        $payload = [
            'operacion' => 'generar_comprobante',
            'tipo_de_comprobante' => 2,
            'serie' => $this->empresa->serie_boleta,
            'numero' => $numero,
            'sunat_transaction' => 1,
            'cliente_tipo_de_documento' => $data['cliente_tipo_documento'] ?? '1',
            'cliente_numero_de_documento' => $data['cliente_documento'],
            'cliente_denominacion' => $data['cliente_nombre'],
            'cliente_direccion' => $data['cliente_direccion'] ?? '',
            'cliente_email' => $data['cliente_email'] ?? '',
            'fecha_de_emision' => date('d-m-Y'),
            'moneda' => 1,
            'porcentaje_de_igv' => 18.00,
            'total_gravada' => $data['total_gravada'],
            'total_igv' => $data['total_igv'],
            'total' => $data['total'],
            'enviar_automaticamente_a_la_sunat' => true,
            'enviar_automaticamente_al_cliente' => !empty($data['cliente_email']),
            'items' => $data['items'],
        ];

        return $this->enviarComprobante($payload);
    }

    public function emitirFactura(array $data)
    {
        $numero = $this->empresa->siguienteNumeroFactura();
        
        $payload = [
            'operacion' => 'generar_comprobante',
            'tipo_de_comprobante' => 1,
            'serie' => $this->empresa->serie_factura,
            'numero' => $numero,
            'sunat_transaction' => 1,
            'cliente_tipo_de_documento' => '6',
            'cliente_numero_de_documento' => $data['cliente_ruc'],
            'cliente_denominacion' => $data['cliente_razon_social'],
            'cliente_direccion' => $data['cliente_direccion'],
            'cliente_email' => $data['cliente_email'] ?? '',
            'fecha_de_emision' => date('d-m-Y'),
            'moneda' => 1,
            'porcentaje_de_igv' => 18.00,
            'total_gravada' => $data['total_gravada'],
            'total_igv' => $data['total_igv'],
            'total' => $data['total'],
            'enviar_automaticamente_a_la_sunat' => true,
            'enviar_automaticamente_al_cliente' => !empty($data['cliente_email']),
            'items' => $data['items'],
        ];

        return $this->enviarComprobante($payload);
    }

    private function enviarComprobante(array $payload)
    {
        try {
            Log::info('Nubefact Request', [
                'url' => $this->baseUrl,
                'payload' => $payload
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Token token="' . $this->empresa->nubefact_token . '"',
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl, $payload);

            $result = $response->json();

            Log::info('Nubefact Response', [
                'status' => $response->status(),
                'body' => $result
            ]);

            if ($response->successful() && isset($result['errors']) && $result['errors'] === false) {
                return [
                    'success' => true,
                    'data' => $result,
                    'enlace_pdf' => $result['enlace_del_pdf'] ?? null,
                    'enlace_xml' => $result['enlace_del_xml'] ?? null,
                    'aceptada_por_sunat' => $result['aceptada_por_sunat'] ?? false,
                    'codigo_hash' => $result['codigo_hash'] ?? '',
                    'serie' => $payload['serie'],
                    'numero' => $payload['numero'],
                ];
            }

            return [
                'success' => false,
                'error' => isset($result['errors']) ? json_encode($result['errors']) : ($response->body() ?? 'Error desconocido'),
            ];

        } catch (\Exception $e) {
            Log::error('Error en Nubefact', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
