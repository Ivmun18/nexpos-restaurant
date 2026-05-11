<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use App\Models\CajaMinimarket;
use App\Models\Venta;
use Illuminate\Http\Request;
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
            ->whereDate('created_at', $hoy)
            ->get();

        $totalEfectivo = $ventasHoy->where('metodo_pago', 'efectivo')->sum('total_gravado');
        $totalYape     = $ventasHoy->where('metodo_pago', 'yape')->sum('total_gravado');
        $totalPlin     = $ventasHoy->where('metodo_pago', 'plin')->sum('total_gravado');
        $totalTarjeta  = $ventasHoy->where('metodo_pago', 'tarjeta')->sum('total_gravado');
        $totalDia      = $ventasHoy->sum('total_gravado');

        // Ventas por hora
        $ventasPorHora = Venta::where('empresa_id', $empresaId)
            ->whereDate('created_at', $hoy)
            ->selectRaw('HOUR(created_at) as hora, COUNT(*) as cantidad, SUM(total_gravado) as total')
            ->groupBy('hora')
            ->orderBy('hora')
            ->get();

        // Últimas ventas
        $ultimasVentas = Venta::where('empresa_id', $empresaId)
            ->whereDate('created_at', $hoy)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Historial de cajas
        $historialCajas = CajaMinimarket::where('empresa_id', $empresaId)
            ->where('estado', 'cerrada')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Farmacia/Caja', [
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
            ->whereDate('created_at', $hoy)->get();

        $totalEfectivo = $ventasHoy->where('metodo_pago', 'efectivo')->sum('total_gravado');
        $totalYape     = $ventasHoy->where('metodo_pago', 'yape')->sum('total_gravado');
        $totalPlin     = $ventasHoy->where('metodo_pago', 'plin')->sum('total_gravado');
        $totalTarjeta  = $ventasHoy->where('metodo_pago', 'tarjeta')->sum('total_gravado');
        $totalVentas   = $ventasHoy->sum('total_gravado');

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

        return redirect()->route('minimarket.caja')->with('success', 'Caja cerrada correctamente');
    }
}