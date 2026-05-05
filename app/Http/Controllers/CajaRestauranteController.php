<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\CajaRestaurante;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CajaRestauranteController extends Controller
{
    // Vista de cobro de una mesa
    public function show(Mesa $mesa): Response
    {
        $pedidos = Pedido::with('detalles')
            ->where('mesa_id', $mesa->id)
            ->whereIn('estado', ['enviado', 'listo'])
            ->orderBy('numero_ronda')
            ->get();

        $total = $pedidos->sum('total');

        return Inertia::render('CajaRestaurante/Show', [
            'mesa'    => $mesa,
            'pedidos' => $pedidos,
            'total'   => $total,
        ]);
    }

    // Registrar pago y liberar mesa
    public function cobrar(Request $request, Mesa $mesa)
    {
        $request->validate([
            'metodo_pago'  => 'required|in:efectivo,tarjeta,yape,plin',
            'monto_pagado' => 'required|numeric|min:0',
            'notas'        => 'nullable|string',
        ]);

        $pedidos = Pedido::where('mesa_id', $mesa->id)
            ->whereIn('estado', ['enviado', 'listo'])
            ->get();

        $total  = $pedidos->sum('total');
        $vuelto = max(0, $request->monto_pagado - $total);

        // Registrar en caja
        $caja = CajaRestaurante::create([
            'mesa_id'      => $mesa->id,
            'user_id'      => auth()->id(),
            'total'        => $total,
            'monto_pagado' => $request->monto_pagado,
            'vuelto'       => $vuelto,
            'metodo_pago'  => $request->metodo_pago,
            'notas'        => $request->notas,
        ]);

        // Cerrar pedidos
        Pedido::where('mesa_id', $mesa->id)
            ->whereIn('estado', ['enviado', 'listo'])
            ->update(['estado' => 'cerrado']);

        // Liberar mesa
        $mesa->update(['estado' => 'libre']);

        // Redirigir a emitir comprobante
        return redirect()->route('comprobantes.crear', $caja)
            ->with('success', "Mesa {$mesa->numero} cobrada. Vuelto: S/ {$vuelto}");
    }
}
