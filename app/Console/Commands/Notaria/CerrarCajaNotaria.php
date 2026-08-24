<?php

namespace App\Console\Commands\Notaria;

use App\Models\SesionCaja;
use Illuminate\Console\Command;

class CerrarCajaNotaria extends Command
{
    protected $signature = 'notaria:cerrar-caja';
    protected $description = 'Cierra automáticamente las sesiones de caja abiertas de empresas de tipo notaría (cierre diario 9 PM)';

    public function handle()
    {
        $sesiones = SesionCaja::join('caja', 'sesiones_caja.caja_id', '=', 'caja.id')
            ->join('empresas', 'caja.empresa_id', '=', 'empresas.id')
            ->where('sesiones_caja.estado', 'abierta')
            ->where('empresas.industry_type', 'notaria')
            ->select('sesiones_caja.*')
            ->with('movimientos')
            ->get();

        if ($sesiones->isEmpty()) {
            $this->info('No hay cajas abiertas de notarías para cerrar.');
            return;
        }

        foreach ($sesiones as $sesion) {
            // Misma lógica que CajaNotariaController::cerrar(), sin monto_real
            // (no hay cajero presente para contar el efectivo), por lo que el
            // sistema se cierra contra sí mismo y la diferencia queda en 0.
            $ingresos     = $sesion->movimientos->where('tipo', 'ingreso')->sum('monto');
            $egresos      = $sesion->movimientos->where('tipo', 'egreso')->sum('monto');
            $montoSistema = round($sesion->monto_apertura + $ingresos - $egresos, 2);

            $sesion->update([
                'fecha_cierre'         => now(),
                'monto_cierre_sistema' => $montoSistema,
                'monto_cierre_real'    => $montoSistema,
                'diferencia'           => 0,
                'estado'               => 'cerrada',
                'observaciones'        => 'Cierre automático 9 PM - cerrado por el sistema (notaria:cerrar-caja), sin conteo manual de efectivo.',
            ]);

            $this->info("Sesión #{$sesion->id} (caja {$sesion->caja_id}) cerrada automáticamente. Saldo sistema: S/ {$montoSistema}");
        }

        $this->info($sesiones->count() . ' caja(s) de notaría cerrada(s) automáticamente.');
    }
}
