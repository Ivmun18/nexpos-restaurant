<?php

namespace App\Console\Commands;

use App\Models\Venta;
use App\Services\Sunat\ApiSunatEstadoService;
use Illuminate\Console\Command;

class SincronizarVentasApiSunat extends Command
{
    protected $signature = 'ventas:sincronizar-pendientes';
    protected $description = 'Consulta en ApiSunat el estado de las ventas (Minimarket/Farmacia/Ferretería) que quedaron en nubefact_estado=pendiente y actualiza su estado';

    public function handle(ApiSunatEstadoService $service): int
    {
        $pendientes = Venta::with('empresa')
            ->where('nubefact_estado', 'pendiente')
            ->get();

        $this->info("Revisando {$pendientes->count()} ventas pendientes...");

        foreach ($pendientes as $venta) {
            if (!$venta->empresa) {
                continue;
            }

            $resultado = $service->consultarYActualizar($venta, $venta->empresa);

            if (!($resultado['success'] ?? false)) {
                $this->warn("Venta {$venta->id} ({$venta->serie}-{$venta->correlativo}): error - " . ($resultado['mensaje'] ?? 'desconocido'));
                continue;
            }

            $this->line("Venta {$venta->id} ({$venta->serie}-{$venta->correlativo}): {$resultado['estado']}");
        }

        $this->info('Sincronización de ventas completada.');

        return self::SUCCESS;
    }
}
