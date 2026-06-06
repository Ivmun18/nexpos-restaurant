<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\CajaMinimarket;
use App\Models\HotelPago;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CajaHotelController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $cajaAbierta = CajaMinimarket::where('empresa_id', $empresaId)
            ->where('estado', 'abierta')
            ->with('usuario')
            ->latest()->first();

        $historial = CajaMinimarket::where('empresa_id', $empresaId)
            ->where('estado', 'cerrada')
            ->with('usuario')
            ->orderByDesc('created_at')
            ->take(10)->get();

        // Pagos del día si hay caja abierta
        $pagosHoy = [];
        if ($cajaAbierta) {
            $pagosHoy = HotelPago::whereHas('reserva', fn($q) => $q->where('empresa_id', $empresaId))
                ->whereDate('created_at', Carbon::today())
                ->get();
        }

        return Inertia::render('Hotel/Caja', compact('cajaAbierta', 'historial', 'pagosHoy'));
    }

    public function abrir(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $cajaExistente = CajaMinimarket::where('empresa_id', $empresaId)
            ->where('estado', 'abierta')->first();

        if ($cajaExistente) {
            return back()->with('error', 'Ya hay una caja abierta.');
        }

        CajaMinimarket::create([
            'empresa_id'    => $empresaId,
            'usuario_id'    => auth()->id(),
            'monto_inicial' => $request->monto_inicial ?? 0,
            'estado'        => 'abierta',
            'apertura_at'   => now(),
        ]);

        return redirect()->route('hotel.caja')->with('success', 'Caja abierta correctamente.');
    }

    public function cerrar(Request $request, CajaMinimarket $caja)
    {
        $empresaId = auth()->user()->empresa_id;

        if ($caja->empresa_id !== $empresaId) abort(403);

        // Calcular totales del día desde pagos hotel
        $pagos = HotelPago::whereHas('reserva', fn($q) => $q->where('empresa_id', $empresaId))
            ->whereDate('created_at', Carbon::today())
            ->get();

        $totalEfectivo     = $pagos->where('metodo_pago', 'efectivo')->sum('monto');
        $totalYape         = $pagos->where('metodo_pago', 'yape')->sum('monto');
        $totalPlin         = $pagos->where('metodo_pago', 'plin')->sum('monto');
        $totalTarjeta      = $pagos->where('metodo_pago', 'tarjeta')->sum('monto');
        $totalTransferencia= $pagos->where('metodo_pago', 'transferencia')->sum('monto');
        $totalVentas       = $pagos->sum('monto');
        $montoFinal        = $request->monto_final ?? 0;
        $efectivoEsperado  = $caja->monto_inicial + $totalEfectivo;
        $diferencia        = $montoFinal - $efectivoEsperado;

        $caja->update([
            'total_efectivo'  => $totalEfectivo,
            'total_yape'      => $totalYape,
            'total_plin'      => $totalPlin,
            'total_tarjeta'   => $totalTarjeta,
            'total_ventas'    => $totalVentas,
            'cantidad_ventas' => $pagos->count(),
            'monto_final'     => $montoFinal,
            'diferencia'      => $diferencia,
            'observaciones'   => $request->observaciones,
            'estado'          => 'cerrada',
            'cierre_at'       => now(),
        ]);

        return redirect()->route('hotel.caja')->with('success', 'Caja cerrada. Diferencia: S/ ' . number_format($diferencia, 2));
    }
}
