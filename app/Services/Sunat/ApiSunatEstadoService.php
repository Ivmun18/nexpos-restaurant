<?php

namespace App\Services\Sunat;

use App\Models\Empresa;
use App\Models\Venta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiSunatEstadoService
{
    /**
     * Consulta en ApiSunat el estado actual de una venta que quedó en
     * nubefact_estado = 'pendiente' y actualiza el registro si ya se
     * resolvió (aceptado/rechazado). No hace nada si sigue pendiente.
     *
     * Usa GET /documents/getAll por type/serie/number — igual que
     * ComprobanteSunatController::consultarEstado (Notaría), que es el
     * patron probado en producción. /personas/v1/getDocument no existe
     * (da 404 "no method handling POST /v1/getDocument").
     */
    public function consultarYActualizar(Venta $venta, Empresa $empresa): array
    {
        if (empty($empresa->apisunat_ruc) || empty($empresa->apisunat_token)) {
            return ['success' => false, 'mensaje' => 'Empresa sin credenciales ApiSunat'];
        }

        try {
            $response = Http::timeout(30)->get('https://back.apisunat.com/documents/getAll', [
                'personaId'    => $empresa->apisunat_ruc,
                'personaToken' => $empresa->apisunat_token,
                'type'         => $venta->tipo_comprobante,
                'serie'        => $venta->serie,
                'number'       => sprintf('%08d', $venta->correlativo),
                'limit'        => 1,
            ]);

            if (!$response->successful()) {
                Log::warning("ApiSunatEstadoService: venta {$venta->id} ({$venta->serie}-{$venta->correlativo}): HTTP {$response->status()} - " . $response->body());
                return ['success' => false, 'mensaje' => 'Error HTTP ' . $response->status()];
            }

            $data = $response->json()[0] ?? null;
            if (!$data) {
                return ['success' => true, 'estado' => 'pendiente', 'status_apisunat' => null];
            }

            Log::info("ApiSunatEstadoService: venta {$venta->id} ({$venta->serie}-{$venta->correlativo}): " . json_encode($data));

            $status = $data['status'] ?? null;

            $updates = [];
            if ($status === 'ACEPTADO') {
                $updates = ['nubefact_estado' => 'aceptado', 'estado' => 'aceptado', 'observaciones' => json_encode($data)];
            } elseif ($status === 'RECHAZADO' || $status === 'EXCEPCION') {
                $updates = ['nubefact_estado' => 'rechazado', 'observaciones' => json_encode($data)];
            }

            if ($updates) {
                $pdfUrl = $data['pdf']['80mm'] ?? $data['pdf']['A4'] ?? null;
                if ($pdfUrl) {
                    $updates['nubefact_id'] = $pdfUrl;
                }
                $venta->update($updates);
            }

            return ['success' => true, 'estado' => $venta->nubefact_estado, 'status_apisunat' => $status];
        } catch (\Exception $e) {
            Log::error("ApiSunatEstadoService: error consultando venta {$venta->id}: " . $e->getMessage());
            return ['success' => false, 'mensaje' => $e->getMessage()];
        }
    }
}
