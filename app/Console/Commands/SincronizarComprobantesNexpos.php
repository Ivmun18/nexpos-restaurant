<?php

namespace App\Console\Commands;

use App\Http\Controllers\ComprobanteSunatController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SincronizarComprobantesNexpos extends Command
{
    protected $signature = 'nexpos:sincronizar-comprobantes';
    protected $description = 'Consulta en ApiSunat el estado de los comprobantes pendientes de todas las empresas y actualiza su estado';

    public function handle(ComprobanteSunatController $controller)
    {
        $pendientes = DB::table('comprobantes_sunat')
            ->where('estado', 'pendiente')
            ->get();

        $this->info("Revisando {$pendientes->count()} comprobantes pendientes...");

        foreach ($pendientes as $comp) {
            $response = $controller->consultarEstado($comp->id);
            $data     = json_decode($response->getContent(), true);

            if ($data['success'] ?? false) {
                $this->line("Comprobante {$comp->id} ({$comp->serie}-{$comp->numero}): {$data['estado']}");
            } else {
                $this->warn("Comprobante {$comp->id}: error - " . ($data['mensaje'] ?? 'desconocido'));
            }
        }

        $this->info('Sincronización de comprobantes completada.');
    }
}
