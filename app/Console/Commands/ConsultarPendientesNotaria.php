<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConsultarPendientesNotaria extends Command
{
    protected $signature   = 'notaria:consultar-pendientes';
    protected $description = 'Consulta en ApiSunat el estado de comprobantes notariales pendientes (empresa_id 15) y actualiza su estado';

    public function handle()
    {
        $pendientes = DB::table('comprobantes_sunat')
            ->where('empresa_id', 15)
            ->where('estado', 'pendiente')
            ->whereNotNull('apisunat_document_id')
            ->get();

        $this->info("Revisando {$pendientes->count()} comprobantes pendientes...");

        foreach ($pendientes as $comp) {
            try {
                $response = Http::timeout(30)
                    ->get("https://back.apisunat.com/documents/{$comp->apisunat_document_id}/getById");

                if (!$response->successful()) {
                    Log::warning("notaria:consultar-pendientes: HTTP {$response->status()} para comprobante {$comp->id} (doc {$comp->apisunat_document_id}): " . $response->body());
                    continue;
                }

                $status = $response->json('status');
                Log::info("notaria:consultar-pendientes: respuesta comprobante {$comp->id}: " . $response->body());

                if ($status === 'ACEPTADO') {
                    DB::table('comprobantes_sunat')->where('id', $comp->id)->update([
                        'estado'             => 'aceptado',
                        'aceptada_por_sunat' => 1,
                        'updated_at'         => now(),
                    ]);
                    $this->info("Comprobante {$comp->id} ({$comp->serie}-{$comp->numero}): ACEPTADO");
                } elseif ($status === 'RECHAZADO') {
                    DB::table('comprobantes_sunat')->where('id', $comp->id)->update([
                        'estado'     => 'rechazado',
                        'updated_at' => now(),
                    ]);
                    $this->warn("Comprobante {$comp->id} ({$comp->serie}-{$comp->numero}): RECHAZADO");
                } else {
                    $this->line("Comprobante {$comp->id}: sigue pendiente (status={$status})");
                }
            } catch (\Exception $e) {
                Log::error("notaria:consultar-pendientes: error en comprobante {$comp->id}: " . $e->getMessage());
            }
        }

        $this->info('Consulta de pendientes completada.');
    }
}
