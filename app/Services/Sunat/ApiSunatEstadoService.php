<?php

namespace App\Services\Sunat;

use App\Models\Empresa;
use App\Models\Venta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiSunatEstadoService
{
    private const ESTADOS_ACEPTADO = ['ACEPTADO', 'ACEPTADO CON OBSERVACIONES', 'ACEPTADA'];
    private const ESTADOS_RECHAZADO = ['RECHAZADO', 'RECHAZADA'];

    /**
     * Consulta en ApiSunat el estado actual de una venta que quedó en
     * nubefact_estado = 'pendiente' y actualiza el registro si ya se
     * resolvió (aceptado/rechazado). No hace nada si sigue pendiente.
     */
    public function consultarYActualizar(Venta $venta, Empresa $empresa): array
    {
        if (empty($empresa->apisunat_ruc) || empty($empresa->apisunat_token)) {
            return ['success' => false, 'mensaje' => 'Empresa sin credenciales ApiSunat'];
        }

        $fileName = $empresa->ruc . '-' . $venta->tipo_comprobante . '-' . $venta->serie
            . '-' . str_pad((string) $venta->correlativo, 8, '0', STR_PAD_LEFT);

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(30)
                ->post('https://back.apisunat.com/personas/v1/getDocument', [
                    'personaId'    => $empresa->apisunat_ruc,
                    'personaToken' => $empresa->apisunat_token,
                    'fileName'     => $fileName,
                ]);

            $data = $response->json();
            Log::info("ApiSunatEstadoService: venta {$venta->id} ({$fileName}): " . json_encode($data));

            $status = strtoupper((string) ($data['status'] ?? ''));
            $aceptada  = $response->successful() && in_array($status, self::ESTADOS_ACEPTADO, true);
            $rechazada = $response->successful() && in_array($status, self::ESTADOS_RECHAZADO, true);

            if (!$aceptada && !$rechazada) {
                return ['success' => true, 'estado' => 'pendiente', 'status_apisunat' => $status];
            }

            $pdfUrl = $data['pdf']['80mm'] ?? $data['pdf']['A4'] ?? $venta->nubefact_id;

            $venta->update([
                'nubefact_id'     => $pdfUrl,
                'nubefact_estado' => $aceptada ? 'aceptado' : 'rechazado',
                'estado'          => $aceptada ? 'aceptado' : $venta->estado,
                'observaciones'   => json_encode($data),
            ]);

            return ['success' => true, 'estado' => $venta->nubefact_estado, 'status_apisunat' => $status];
        } catch (\Exception $e) {
            Log::error("ApiSunatEstadoService: error consultando venta {$venta->id}: " . $e->getMessage());
            return ['success' => false, 'mensaje' => $e->getMessage()];
        }
    }
}
