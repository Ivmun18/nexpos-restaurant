<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SincronizarCajaNotaria extends Command
{
    protected $signature   = 'notaria:sincronizar-caja';
    protected $description = 'Sincroniza caja_movimientos con comprobantes_sunat aceptados';

    public function handle()
    {
        $empresas = DB::table('empresas')
            ->whereJsonContains('modules_enabled', 'notaria')
            ->pluck('id');

        foreach ($empresas as $empresaId) {
            $sesion = DB::table('sesiones_caja')
                ->join('caja', 'sesiones_caja.caja_id', '=', 'caja.id')
                ->where('sesiones_caja.estado', 'abierta')
                ->where('caja.empresa_id', $empresaId)
                ->select('sesiones_caja.*')
                ->first();

            if (!$sesion) continue;

            // Obtener IDs de comprobantes ya registrados en caja
            $yaRegistrados = DB::table('caja_movimientos')
                ->where('sesion_id', $sesion->id)
                ->where('tipo', 'ingreso')
                ->whereNotNull('referencia_id')
                ->pluck('referencia_id')
                ->toArray();

            // Comprobantes aceptados no registrados aún
            $comps = DB::table('comprobantes_sunat')
                ->where('empresa_id', $empresaId)
                ->whereDate('fecha_emision', today())
                ->whereIn('estado', ['aceptado', 'emitido'])
                ->where('total', '>', 0)
                ->whereNotIn('id', $yaRegistrados)
                ->get();

            foreach ($comps as $c) {
                DB::table('caja_movimientos')->insert([
                    'sesion_id'    => $sesion->id,
                    'usuario_id'   => $sesion->usuario_id,
                    'tipo'         => 'ingreso',
                    'concepto'     => 'Servicio rapido: ' . ($c->enlace_cdr ?? 'Servicio notarial') . ' (efectivo)',
                    'referencia_id'=> $c->id,
                    'monto'        => $c->total,
                    'metodo_pago'  => 'efectivo',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            // Eliminar movimientos de comprobantes anulados/rechazados
            $anulados = DB::table('comprobantes_sunat')
                ->where('empresa_id', $empresaId)
                ->whereIn('estado', ['anulado', 'rechazado'])
                ->pluck('id')
                ->toArray();

            if (!empty($anulados)) {
                DB::table('caja_movimientos')
                    ->where('sesion_id', $sesion->id)
                    ->whereIn('referencia_id', $anulados)
                    ->delete();
            }

            $this->info("Empresa $empresaId: " . count($comps) . " movimientos sincronizados.");
        }

        $this->info('Sincronización completada.');
    }
}
