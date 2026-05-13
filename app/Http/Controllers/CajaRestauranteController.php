<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\CajaRestaurante;
use App\Models\CajaMovimiento;
use App\Models\SesionCaja;
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
            'metodo_pago'       => 'required|in:efectivo,tarjeta,yape,plin',
            'monto_pagado'      => 'required|numeric|min:0',
            'notas'             => 'nullable|string',
            'tipo_comprobante'  => 'nullable|in:boleta,factura,ninguno',
        ]);

        $pedidos = Pedido::where('mesa_id', $mesa->id)
            ->whereIn('estado', ['enviado', 'listo'])
            ->get();

        $total  = $pedidos->sum('total');
        $vuelto = max(0, $request->monto_pagado - $total);

        // Registrar en caja
        $caja = CajaRestaurante::create([
            'mesa_id'          => $mesa->id,
            'user_id'          => auth()->id(),
            'total'            => $total,
            'monto_pagado'     => $request->monto_pagado,
            'vuelto'           => $vuelto,
            'metodo_pago'      => $request->metodo_pago,
            'tipo_comprobante' => $request->tipo_comprobante ?? 'ninguno',
            'notas'            => $request->notas,
        ]);

        // Registrar movimiento en caja si hay sesión abierta
        $sesion = SesionCaja::where('estado', 'abierta')->first();
        if ($sesion) {
            CajaMovimiento::create([
                'sesion_id'    => $sesion->id,
                'usuario_id'   => auth()->id(),
                'tipo'         => 'ingreso',
                'concepto'     => 'Cobro Mesa ' . $mesa->numero . ' (' . $request->metodo_pago . ')',
                'referencia_id'=> $caja->id,
                'monto'        => $total,
                'observaciones'=> $request->notas ?? null,
            ]);
        }

        // Cerrar pedidos y asociar al cobro
        Pedido::where('mesa_id', $mesa->id)
            ->whereIn('estado', ['enviado', 'listo'])
            ->update(['estado' => 'cerrado', 'caja_restaurante_id' => $caja->id]);

        // Liberar mesa
        $mesa->update(['estado' => 'libre']);

        // Redirigir a emitir comprobante
        $tipo = $request->tipo_comprobante ?? 'boleta';

        if ($tipo === 'ninguno') {
            return redirect()->route('mesas.index')
                ->with('success', "Mesa {$mesa->numero} cobrada. Vuelto: S/ {$vuelto}");
        }

        return redirect()->route('comprobantes.crear', $caja)
            ->with('success', "Mesa {$mesa->numero} cobrada. Vuelto: S/ {$vuelto}")
            ->with('tipo_comprobante', $tipo);
    }
}
