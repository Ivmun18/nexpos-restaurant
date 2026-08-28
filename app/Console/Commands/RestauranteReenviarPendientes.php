<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RestauranteReenviarPendientes extends Command
{
    protected $signature = 'restaurante:reenviar-pendientes {--empresa=23} {--dry-run}';
    protected $description = 'Consulta a ApiSunat (GET documents/{documentId}/getById) el estado real de los comprobantes en estado=pendiente de una empresa, para reconciliarlos. No reenvía sendBill: reenviar un documento que SUNAT ya está procesando lo rechaza por numeración repetida (visto en producción el 2026-08-28).';

    private const ENDPOINT_CONSULTAR = 'https://back.apisunat.com/documents';

    public function handle(): int
    {
        $empresaId = (int) $this->option('empresa');
        $dryRun    = (bool) $this->option('dry-run');

        $empresa = Empresa::find($empresaId);
        if (! $empresa) {
            $this->error("Empresa {$empresaId} no encontrada.");
            return self::FAILURE;
        }

        $pendientes = DB::table('comprobantes_sunat')
            ->where('empresa_id', $empresaId)
            ->where('estado', 'pendiente')
            ->orderBy('id')
            ->get();

        $this->info("Reconciliando {$pendientes->count()} comprobante(s) pendiente(s) de empresa {$empresaId}" . ($dryRun ? ' [DRY-RUN]' : ''));

        foreach ($pendientes as $comp) {
            if (! $comp->apisunat_document_id) {
                $this->warn("Comprobante {$comp->id} ({$comp->serie}-{$comp->numero}): sin apisunat_document_id, no se puede consultar su estado real por este medio. Requiere revisión manual (buscar por serie/número en el panel de ApiSunat).");
                continue;
            }

            if ($dryRun) {
                $this->line("[DRY-RUN] {$comp->id} ({$comp->serie}-{$comp->numero}) documentId={$comp->apisunat_document_id}");
                continue;
            }

            try {
                $response = Http::timeout(30)->get(self::ENDPOINT_CONSULTAR . "/{$comp->apisunat_document_id}/getById");

                if (! $response->successful()) {
                    $this->error("Comprobante {$comp->id}: ApiSunat respondió HTTP {$response->status()} al consultar.");
                    Log::error("restaurante:reenviar-pendientes error HTTP comprobante {$comp->id}: " . $response->body());
                    continue;
                }

                $data = $response->json() ?? [];
                Log::info("restaurante:reenviar-pendientes consulta comprobante {$comp->id}: " . json_encode($data));

                $estadosAceptado = ['ACEPTADO', 'ACEPTADO CON OBSERVACIONES'];
                $status = $data['status'] ?? null;

                if (in_array($status, $estadosAceptado, true)) {
                    $pdfUrl = $data['pdf']['80mm'] ?? $data['pdf']['A4'] ?? null;
                    DB::table('comprobantes_sunat')->where('id', $comp->id)->update([
                        'aceptada_por_sunat' => 1,
                        'sunat_descripcion'  => 'Aceptada',
                        'enlace_pdf'         => $pdfUrl ?? $comp->enlace_pdf,
                        'estado'             => 'aceptado',
                        'updated_at'         => now(),
                    ]);
                    $this->info("Comprobante {$comp->id} ({$comp->serie}-{$comp->numero}): ACEPTADO");
                } elseif ($status === 'PENDIENTE' || $status === null) {
                    $this->line("Comprobante {$comp->id} ({$comp->serie}-{$comp->numero}): sigue PENDIENTE, sin cambios.");
                } else {
                    DB::table('comprobantes_sunat')->where('id', $comp->id)->update([
                        'sunat_descripcion' => json_encode($data),
                        'estado'            => 'rechazado',
                        'updated_at'        => now(),
                    ]);
                    $this->info("Comprobante {$comp->id} ({$comp->serie}-{$comp->numero}): RECHAZADO ({$status})");
                }
            } catch (\Throwable $e) {
                Log::error("restaurante:reenviar-pendientes error comprobante {$comp->id}: " . $e->getMessage());
                $this->error("Comprobante {$comp->id}: {$e->getMessage()}");
            }
        }

        $this->info('Reconciliación completada.');
        return self::SUCCESS;
    }
}
