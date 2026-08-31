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
        $empresaId = auth()->user()->empresa_id;

        // Sin este chequeo, cualquier usuario autenticado podía cobrar (y
        // disparar la emisión SUNAT con las credenciales de SU empresa) la
        // venta de otra empresa con solo cambiar el id en la URL.
        if ($venta->empresa_id !== $empresaId) {
            abort(403);
        }

        if ($venta->estado !== 'pendiente') {
            return back()->with('error', 'Esta venta ya fue procesada.');
        }

        $cajaAbierta = \App\Models\CajaMinimarket::where('empresa_id', $empresaId)
            ->where('estado', 'abierta')
            ->exists();

        if (!$cajaAbierta) {
            return back()->with('error', 'No hay una caja abierta. Abre caja antes de cobrar.');
        }

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
