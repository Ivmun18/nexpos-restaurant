<?php

namespace App\Http\Controllers\Minimarket;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CajeroMinimarketController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $cajaAbierta = \App\Models\CajaMinimarket::where('empresa_id', $empresaId)
            ->where('estado', 'abierta')
            ->first();

        if (!$cajaAbierta) {
            return redirect()->route('minimarket.caja')->with('warning', '⚠️ Debes abrir la caja antes de cobrar.');
        }

        $ventasPendientes = Venta::with('detalle')
            ->where('empresa_id', $empresaId)
            ->where('estado', 'pendiente')
            ->whereDate('fecha_emision', now()->toDateString())
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Minimarket/Cajero', [
            'ventas_pendientes' => $ventasPendientes,
            'caja_abierta'      => $cajaAbierta,
        ]);
    }

    public function cobrar(Request $request, Venta $venta)
    {
        $request->validate([
            'metodo_pago' => 'required|string',
        ]);

        $venta->update([
            'metodo_pago' => $request->metodo_pago,
            'estado'      => 'emitido',
        ]);

        $empresa = auth()->user()->empresa;
        if ($empresa->nubefact_token) {
            (new \App\Services\FacturacionService())->emitir($venta, $empresa);
        }

        return back()->with('success', '✅ Venta cobrada correctamente.');
    }
}
