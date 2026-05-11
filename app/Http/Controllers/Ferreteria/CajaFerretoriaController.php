<?php

namespace App\Http\Controllers\Ferreteria;

use App\Http\Controllers\Controller;
use App\Models\CajaMinimarket as Caja;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;

class CajaFerretoriaController extends Controller
{
    public function index()
    {
        $empresa_id  = auth()->user()->empresa_id;
        $cajaAbierta = Caja::where('empresa_id', $empresa_id)->where('estado', 'abierta')->latest()->first();
        $historial = Caja::where('empresa_id', $empresa_id)
            ->orderByDesc('created_at')->take(10)->get()
            ->map(function($caja) use ($empresa_id) {
                $ventas = \App\Models\Venta::where('empresa_id', $empresa_id)
                    ->whereDate('fecha_emision', \Carbon\Carbon::parse($caja->apertura_at)->toDateString())
                    ->where('estado', '!=', 'anulado')->get();
                $caja->cantidad_ventas = $ventas->count();
                $caja->total_ventas    = round($ventas->sum('total'), 2);
                $caja->total_efectivo  = round($ventas->where('metodo_pago','efectivo')->sum('total'), 2);
                $caja->total_yape      = round($ventas->where('metodo_pago','yape')->sum('total'), 2);
                $caja->total_plin      = round($ventas->where('metodo_pago','plin')->sum('total'), 2);
                $caja->total_tarjeta   = round($ventas->where('metodo_pago','tarjeta')->sum('total'), 2);
                $caja->diferencia      = round(($caja->monto_final ?? 0) - ($caja->monto_inicial + $caja->total_efectivo), 2);
                return $caja;
            });

        return Inertia::render('Ferreteria/Caja', compact('cajaAbierta', 'historial'));
    }

    public function abrir(Request $request)
    {
        Caja::create([
            'empresa_id'    => auth()->user()->empresa_id,
            'usuario_id'    => auth()->id(),
            'monto_inicial' => $request->monto_inicial ?? 0,
            'estado'        => 'abierta',
            'apertura_at'   => now(),
        ]);
        return back()->with('success', 'Caja abierta');
    }

    public function cerrar(Request $request, Caja $caja)
    {
        $caja->update([
            'monto_final'   => $request->monto_final ?? 0,
            'diferencia'    => ($request->monto_final ?? 0) - ($caja->monto_inicial + $caja->total_efectivo),
            'observaciones' => $request->observaciones,
            'estado'        => 'cerrada',
            'cierre_at'     => now(),
        ]);
        return back()->with('success', 'Caja cerrada');
    }
}
