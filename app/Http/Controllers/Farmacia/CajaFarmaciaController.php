<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use App\Models\CajaMinimarket;
use App\Models\Venta;
use Illuminate\Http\Request;
use App\Models\AuditoriaLog;
use Carbon\Carbon;
use Inertia\Inertia;

class CajaFarmaciaController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;
        $hoy = now()->toDateString();

        // Caja activa
        $cajaAbierta = CajaMinimarket::where('empresa_id', $empresaId)
            ->where('estado', 'abierta')
            ->latest()->first();

        // Ventas del día
        $ventasHoy = Venta::where('empresa_id', $empresaId)
            ->whereDate('fecha_emision', $hoy)
            ->get();

        $totalEfectivo = $ventasHoy->where('metodo_pago', 'efectivo')->sum('total');
        $totalYape     = $ventasHoy->where('metodo_pago', 'yape')->sum('total');
        $totalPlin     = $ventasHoy->where('metodo_pago', 'plin')->sum('total');
        $totalTarjeta  = $ventasHoy->where('metodo_pago', 'tarjeta')->sum('total');
        $totalDia      = $ventasHoy->sum('total');

        // Ventas por hora
        $ventasPorHora = Venta::where('empresa_id', $empresaId)
            ->whereDate('fecha_emision', $hoy)
            ->selectRaw('HOUR(created_at) as hora, COUNT(*) as cantidad, SUM(total) as total')
            ->groupBy('hora')
            ->orderBy('hora')
            ->get();

        // Últimas ventas
        $ultimasVentas = Venta::where('empresa_id', $empresaId)
            ->whereDate('fecha_emision', $hoy)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Historial de cajas
        $historialCajas = CajaMinimarket::where('empresa_id', $empresaId)
            ->where('estado', 'cerrada')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($caja) use ($empresaId) {
                $ventas = Venta::where('empresa_id', $empresaId)
                    ->whereDate('fecha_emision', Carbon::parse($caja->apertura_at)->toDateString())
                    ->where('estado', '!=', 'anulado')
                    ->get();
                $caja->cantidad_ventas = $ventas->count();
                $caja->total_ventas    = round($ventas->sum('total'), 2);
                $caja->total_efectivo  = round($ventas->where('metodo_pago','efectivo')->sum('total'), 2);
                $caja->total_yape      = round($ventas->where('metodo_pago','yape')->sum('total'), 2);
                $caja->total_plin      = round($ventas->where('metodo_pago','plin')->sum('total'), 2);
                $caja->total_tarjeta   = round($ventas->where('metodo_pago','tarjeta')->sum('total'), 2);
                $caja->diferencia      = round($caja->monto_final - ($caja->monto_inicial + $caja->total_efectivo), 2);
                return $caja;
            });

        return Inertia::render('Farmacia/Caja', [
            'user_rol' => auth()->user()->rol,
            'caja_abierta' => $cajaAbierta,
            'resumen' => [
                'total_dia'      => round($totalDia, 2),
                'total_efectivo' => round($totalEfectivo, 2),
                'total_yape'     => round($totalYape, 2),
                'total_plin'     => round($totalPlin, 2),
                'total_tarjeta'  => round($totalTarjeta, 2),
                'total_ventas'   => $ventasHoy->count(),
            ],
            'ventas_por_hora' => $ventasPorHora,
            'ultimas_ventas'  => $ultimasVentas,
            'historial_cajas' => $historialCajas,
            'fecha'           => now()->locale('es')->isoFormat('dddd D [de] MMMM YYYY'),
        ]);
    }

    public function abrir(Request $request)
    {
        $request->validate([
            'monto_inicial' => 'required|numeric|min:0',
        ]);

        $empresaId = auth()->user()->empresa_id;

        // Verificar que no haya caja abierta
        $cajaExistente = CajaMinimarket::where('empresa_id', $empresaId)
            ->where('estado', 'abierta')->first();

        if ($cajaExistente) {
            return back()->with('error', 'Ya hay una caja abierta.');
        }

        CajaMinimarket::create([
            'empresa_id'    => $empresaId,
            'usuario_id'    => auth()->id(),
            'monto_inicial' => $request->monto_inicial,
            'estado'        => 'abierta',
            'apertura_at'   => now(),
        ]);
        
        \App\Models\AuditoriaLog::registrar(
            'caja',
            'abierta',
            'caja',
            null,
            'Caja farmacia',
            null,
            ['monto_inicial' => $request->monto_inicial ?? 0],
            'Caja abierta con S/ ' . ($request->monto_inicial ?? 0) . ' por ' . auth()->user()->name
        );

        return redirect()->route('minimarket.caja')->with('success', 'Caja abierta correctamente');
    }

    public function cerrar(Request $request, CajaMinimarket $caja)
    {
        $request->validate([
            'monto_final'   => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        $empresaId = auth()->user()->empresa_id;
        $hoy = now()->toDateString();

        $ventasHoy = Venta::where('empresa_id', $empresaId)
            ->whereDate('fecha_emision', $hoy)->get();

        $totalEfectivo = $ventasHoy->where('metodo_pago', 'efectivo')->sum('total');
        $totalYape     = $ventasHoy->where('metodo_pago', 'yape')->sum('total');
        $totalPlin     = $ventasHoy->where('metodo_pago', 'plin')->sum('total');
        $totalTarjeta  = $ventasHoy->where('metodo_pago', 'tarjeta')->sum('total');
        $totalVentas   = $ventasHoy->sum('total');

        $efectivoEsperado = $caja->monto_inicial + $totalEfectivo;
        $diferencia = $request->monto_final - $efectivoEsperado;

        $caja->update([
            'total_efectivo'  => $totalEfectivo,
            'total_yape'      => $totalYape,
            'total_plin'      => $totalPlin,
            'total_tarjeta'   => $totalTarjeta,
            'total_ventas'    => $totalVentas,
            'cantidad_ventas' => $ventasHoy->count(),
            'monto_final'     => $request->monto_final,
            'diferencia'      => $diferencia,
            'observaciones'   => $request->observaciones,
            'estado'          => 'cerrada',
            'cierre_at'       => now(),
        ]);
        
        $diferencia = ($caja->monto_final_real ?? 0) - ($caja->monto_final_sistema ?? 0);
        $severidad = abs($diferencia) > 10 ? 'warning' : 'info';
        
        \App\Models\AuditoriaLog::registrar(
            'caja',
            'cerrada',
            'caja',
            $caja->id,
            'Cierre de caja',
            null,
            ['monto_sistema' => $caja->monto_final_sistema, 'monto_real' => $caja->monto_final_real, 'diferencia' => $diferencia],
            'Caja cerrada por ' . auth()->user()->name . ($diferencia != 0 ? ' · Descuadre: S/ ' . number_format($diferencia, 2) : ''),
            $severidad
        );

        return redirect()->route('minimarket.caja')->with('success', 'Caja cerrada correctamente');
    }

    /**
     * Corregir montos de caja (solo admin).
     * Guarda el valor original antes de sobrescribir.
     */
    public function corregir(\Illuminate\Http\Request $request, \App\Models\CajaMinimarket $caja)
    {
        // Solo admin puede corregir
        if (auth()->user()->rol !== 'admin') {
            return back()->withErrors(['error' => 'Solo el administrador puede corregir cajas']);
        }

        // Validar que la caja pertenezca a la empresa del usuario
        if ($caja->empresa_id !== auth()->user()->empresa_id) {
            abort(403, 'No tienes acceso a esta caja');
        }

        $data = $request->validate([
            'monto_inicial_nuevo' => 'required|numeric|min:0',
            'monto_final_nuevo'   => 'nullable|numeric|min:0',
            'motivo'              => 'required|string|min:10|max:1000',
        ]);

        // Guardar valores originales (solo la primera vez que se corrige)
        $updates = [
            'monto_inicial'      => $data['monto_inicial_nuevo'],
            'motivo_correccion'  => $data['motivo'],
            'corregido_por_id'   => auth()->id(),
            'corregido_at'       => now(),
        ];

        if (is_null($caja->monto_inicial_original)) {
            $updates['monto_inicial_original'] = $caja->monto_inicial;
        }

        // Si se corrige el monto final también
        if (!is_null($data['monto_final_nuevo'])) {
            $updates['monto_final'] = $data['monto_final_nuevo'];
            if (is_null($caja->monto_final_original) && !is_null($caja->monto_final)) {
                $updates['monto_final_original'] = $caja->monto_final;
            }
            // Recalcular diferencia si la caja está cerrada
            if ($caja->estado === 'cerrada') {
                $efectivoEsperado = $data['monto_inicial_nuevo'] + ($caja->total_efectivo ?? 0);
                $updates['diferencia'] = $data['monto_final_nuevo'] - $efectivoEsperado;
            }
        }

        $caja->update($updates);

        // Registrar en auditoría
        \App\Models\AuditoriaLog::registrar(
            'caja',
            'corregida',
            'caja',
            $caja->id,
            'Corrección de caja',
            null,
            [
                'monto_inicial_anterior' => $caja->monto_inicial_original,
                'monto_inicial_nuevo'    => $data['monto_inicial_nuevo'],
                'monto_final_anterior'   => $caja->monto_final_original,
                'monto_final_nuevo'      => $data['monto_final_nuevo'] ?? null,
                'motivo'                 => $data['motivo'],
            ],
            'Caja #' . $caja->id . ' corregida por ' . auth()->user()->name . ' · Motivo: ' . $data['motivo'],
            'warning'
        );

        return back()->with('success', 'Caja corregida correctamente. Registrado en auditoría.');
    }

}