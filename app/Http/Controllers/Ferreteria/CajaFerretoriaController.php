<?php

namespace App\Http\Controllers\Ferreteria;

use App\Http\Controllers\Controller;
use App\Models\CajaMinimarket as Caja;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CajaFerretoriaController extends Controller
{
    public function index()
    {
        $empresa_id  = auth()->user()->empresa_id;
        $cajaAbierta = Caja::where('empresa_id', $empresa_id)->where('estado', 'abierta')->latest()->first();
        $historial   = Caja::where('empresa_id', $empresa_id)->where('estado', 'cerrada')->orderByDesc('created_at')->take(10)->get();
        return Inertia::render('Ferreteria/Caja', compact('cajaAbierta', 'historial'));
    }

    public function abrir(Request $request)
    {
        $request->validate(['monto_inicial' => 'required|numeric|min:0']);
        Caja::create([
            'empresa_id'   => auth()->user()->empresa_id,
            'usuario_id'   => auth()->id(),
            'monto_inicial' => $request->monto_inicial,
            'estado'       => 'abierta',
            'apertura_at'  => now(),
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
