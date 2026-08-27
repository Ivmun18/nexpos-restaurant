<?php

namespace App\Console\Commands;

use App\Http\Controllers\Minimarket\PosMinimarketController;
use App\Models\Empresa;
use App\Models\Venta;
use Illuminate\Console\Command;

class MinimarketReenviarPendiente extends Command
{
    protected $signature = 'minimarket:reenviar-pendiente {venta : ID de la venta (tabla ventas) a reenviar}';

    protected $description = 'Reenvía a SUNAT (vía PosMinimarketController::emitirNubefact) una venta puntual del vertical Minimarket que quedó en estado=pendiente — a diferencia de restaurante:reenviar-pendientes (otra tabla, otro vertical), este apunta a una sola venta de la tabla ventas.';

    public function handle(PosMinimarketController $controller): int
    {
        $venta = Venta::with('detalle')->find((int) $this->argument('venta'));

        if (! $venta) {
            $this->error('Venta no encontrada.');

            return self::FAILURE;
        }

        if ($venta->estado !== 'pendiente') {
            $this->error("La venta {$venta->id} ({$venta->serie}-{$venta->correlativo}) no está en estado 'pendiente' (está en '{$venta->estado}') — no se reenvía para evitar duplicar un envío ya resuelto.");

            return self::FAILURE;
        }

        $empresa = Empresa::find($venta->empresa_id);

        if (! $empresa) {
            $this->error("Empresa {$venta->empresa_id} no encontrada.");

            return self::FAILURE;
        }

        $this->info("Reenviando venta {$venta->id} ({$venta->serie}-{$venta->correlativo}), empresa {$empresa->razon_social} (RUC {$empresa->ruc})...");

        $controller->emitirNubefact($venta, $empresa);

        $venta->refresh();

        $this->info("Resultado: estado={$venta->estado}, nubefact_estado={$venta->nubefact_estado}");
        $this->line((string) $venta->observaciones);

        return self::SUCCESS;
    }
}
