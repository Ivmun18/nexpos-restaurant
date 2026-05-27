<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\CajaRestaurante;
use App\Models\CajaMovimiento;
use App\Models\SesionCaja;
use App\Models\Receta;
use App\Models\InsumoMovimiento;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CajaRestauranteController extends Controller
{
    // Devuelve los IDs de todas las mesas del grupo (principal + unidas)
    private function idsGrupoMesa(Mesa $mesa): array
    {
        // Si esta mesa es secundaria, la principal es su mesa_principal_id
        $principalId = $mesa->mesa_principal_id ?: $mesa->id;
        // El grupo = la principal + todas las que apuntan a ella
        $ids = Mesa::where('id', $principalId)
            ->orWhere('mesa_principal_id', $principalId)
            ->pluck('id')
            ->toArray();
        return $ids;
    }

    // Vista de cobro de una mesa
    public function show(Mesa $mesa): Response
    {
        $mesaIds = $this->idsGrupoMesa($mesa);

        $pedidos = Pedido::with('detalles')
            ->whereIn('mesa_id', $mesaIds)
            ->whereIn('estado', ['enviado', 'listo'])
            ->orderBy('numero_ronda')
            ->get();

        // Total bruto de la mesa (todos los platos)
        $total = $pedidos->sum('total');

        // Separar platos pagados de pendientes (para modo por platos)
        $todosDetalles   = $pedidos->flatMap->detalles->where('anulado', false);
        $totalPagadoPlatos = (float) $todosDetalles->where('pagado', true)->sum('subtotal');
        $totalPendiente    = (float) $todosDetalles->where('pagado', false)->sum('subtotal');
        $platosPendientes  = $todosDetalles->where('pagado', false)->count();
        $platosPagados     = $todosDetalles->where('pagado', true)->count();

        // Acumulado de cobros por PARTES iguales (no cuenta los cobros por platos)
        $pagadoAcumulado = (float) CajaRestaurante::where('mesa_id', $mesa->id)
            ->where('partes_total', '>', 1)
            ->where('cuenta_total', $total)
            ->whereHas('mesa', function ($q) {
                $q->where('estado', 'ocupada');
            })
            ->sum('monto_pagado');

        return Inertia::render('CajaRestaurante/Show', [
            'mesa'              => $mesa,
            'pedidos'           => $pedidos,
            'total'             => $total,
            'pagado_acumulado'  => round($pagadoAcumulado, 2),
            'saldo_pendiente'   => round(max(0, $total - $pagadoAcumulado), 2),
            'total_pendiente'   => round($totalPendiente, 2),
            'total_pagado_platos' => round($totalPagadoPlatos, 2),
            'platos_pendientes' => $platosPendientes,
            'platos_pagados'    => $platosPagados,
        ]);
    }

    public function cobrar(Request $request, Mesa $mesa)
    {
        $request->validate([
            'metodo_pago'       => 'required|in:efectivo,tarjeta,yape,plin',
            'monto_pagado'      => 'required|numeric|min:0',
            'notas'             => 'nullable|string',
            'tipo_comprobante'  => 'nullable|in:boleta,factura,ninguno',
            'partes_total'      => 'nullable|integer|min:1',
            'parte_numero'      => 'nullable|integer|min:1',
        ]);

        $pedidos = Pedido::whereIn('mesa_id', $this->idsGrupoMesa($mesa))
            ->whereIn('estado', ['enviado', 'listo'])
            ->get();

        $total = $pedidos->sum('total');

        $pagadoPrevio = (float) CajaRestaurante::where('mesa_id', $mesa->id)
            ->whereNotNull('cuenta_total')
            ->where('cuenta_total', $total)
            ->whereHas('mesa', function ($q) {
                $q->where('estado', 'ocupada');
            })
            ->sum('monto_pagado');

        $partesTotal = (int) ($request->partes_total ?? 1);
        $parteNumero = (int) ($request->parte_numero ?? 1);

        $montoPagado     = (float) $request->monto_pagado;
        $pagadoAcumulado = $pagadoPrevio + $montoPagado;
        $saldoPendiente  = round($total - $pagadoAcumulado, 2);

        $cuentaSaldada = $pagadoAcumulado >= ($total - 0.01);

        $vuelto = $cuentaSaldada ? round(max(0, $pagadoAcumulado - $total), 2) : 0;

        $caja = CajaRestaurante::create([
            'empresa_id'       => auth()->user()->empresa_id,
            'mesa_id'          => $mesa->id,
            'user_id'          => auth()->id(),
            'total'            => $total,
            'monto_pagado'     => $montoPagado,
            'vuelto'           => $vuelto,
            'metodo_pago'      => $request->metodo_pago,
            'tipo_comprobante' => $request->tipo_comprobante ?? 'ninguno',
            'notas'            => $request->notas,
            'partes_total'     => $partesTotal,
            'parte_numero'     => $parteNumero,
            'cuenta_total'     => $total,
            'pagado_acumulado' => round($pagadoAcumulado, 2),
        ]);

        $sesion = SesionCaja::where('estado', 'abierta')->first();
        if ($sesion) {
            $concepto = 'Cobro Mesa ' . $mesa->numero . ' (' . $request->metodo_pago . ')';
            if ($partesTotal > 1) {
                $concepto .= " - parte {$parteNumero}/{$partesTotal}";
            }
            CajaMovimiento::create([
                'sesion_id'    => $sesion->id,
                'usuario_id'   => auth()->id(),
                'tipo'         => 'ingreso',
                'concepto'     => $concepto,
                'referencia_id'=> $caja->id,
                'monto'        => $montoPagado,
                'observaciones'=> $request->notas ?? null,
            ]);
        }

        if (!$cuentaSaldada) {
            return redirect()->route('caja-restaurante.show', $mesa)
                ->with('success', "Parte {$parteNumero}/{$partesTotal} cobrada (S/ " . number_format($montoPagado, 2) . "). Falta S/ " . number_format($saldoPendiente, 2));
        }

        $pedidosItems = \App\Models\PedidoDetalle::whereIn('pedido_id',
            Pedido::whereIn('mesa_id', $this->idsGrupoMesa($mesa))
                ->whereIn('estado', ['enviado', 'listo'])
                ->pluck('id')
        )->where('anulado', false)->get();

        foreach ($pedidosItems as $item) {
            $recetas = Receta::with('insumo')
                ->where('menu_producto_id', $item->menu_producto_id)
                ->get();

            foreach ($recetas as $receta) {
                $insumo = $receta->insumo;
                $cantidadTotal = $receta->cantidad * $item->cantidad;
                $stockAnterior = $insumo->stock_actual;
                $stockNuevo = max(0, $stockAnterior - $cantidadTotal);

                $insumo->update(['stock_actual' => $stockNuevo]);

                InsumoMovimiento::create([
                    'insumo_id'      => $insumo->id,
                    'user_id'        => auth()->id(),
                    'tipo'           => 'salida',
                    'cantidad'       => $cantidadTotal,
                    'costo_unitario' => $insumo->precio_promedio,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo'    => $stockNuevo,
                    'motivo'         => 'Venta Mesa ' . $mesa->numero,
                ]);
            }
        }

        Pedido::whereIn('mesa_id', $this->idsGrupoMesa($mesa))
            ->whereIn('estado', ['enviado', 'listo'])
            ->update(['estado' => 'cerrado', 'caja_restaurante_id' => $caja->id]);

        Mesa::whereIn('id', $this->idsGrupoMesa($mesa))->update(['estado' => 'libre', 'mesa_principal_id' => null]);

        $tipo = $request->tipo_comprobante ?? 'boleta';

        if ($tipo === 'ninguno') {
            return redirect()->route('mesas.index')
                ->with('success', "Mesa {$mesa->numero} cobrada. Vuelto: S/ {$vuelto}");
        }

        return redirect()->route('comprobantes.crear', $caja)
            ->with('success', "Mesa {$mesa->numero} cobrada. Vuelto: S/ {$vuelto}")
            ->with('tipo_comprobante', $tipo);
    }

    // Cobrar solo los platos seleccionados (division por platos)
    public function cobrarPlatos(Request $request, Mesa $mesa)
    {
        $request->validate([
            'metodo_pago'      => 'required|in:efectivo,tarjeta,yape,plin',
            'detalle_ids'      => 'required|array|min:1',
            'detalle_ids.*'    => 'integer',
            'monto_pagado'     => 'required|numeric|min:0',
            'notas'            => 'nullable|string',
            'tipo_comprobante' => 'nullable|in:boleta,factura,ninguno',
        ]);

        $pedidoIds = Pedido::whereIn('mesa_id', $this->idsGrupoMesa($mesa))
            ->whereIn('estado', ['enviado', 'listo'])
            ->pluck('id');

        $detalles = \App\Models\PedidoDetalle::whereIn('id', $request->detalle_ids)
            ->whereIn('pedido_id', $pedidoIds)
            ->where('pagado', false)
            ->where('anulado', false)
            ->get();

        if ($detalles->isEmpty()) {
            return redirect()->route('caja-restaurante.show', $mesa)
                ->with('error', 'Los platos seleccionados ya estan pagados o no son validos.');
        }

        $subtotal = $detalles->sum('subtotal');
        $vuelto   = max(0, $request->monto_pagado - $subtotal);

        $caja = CajaRestaurante::create([
            'empresa_id'       => auth()->user()->empresa_id,
            'mesa_id'          => $mesa->id,
            'user_id'          => auth()->id(),
            'total'            => $subtotal,
            'monto_pagado'     => $request->monto_pagado,
            'vuelto'           => $vuelto,
            'metodo_pago'      => $request->metodo_pago,
            'tipo_comprobante' => $request->tipo_comprobante ?? 'ninguno',
            'notas'            => $request->notas,
            'partes_total'     => 0,
            'parte_numero'     => 0,
            'cuenta_total'     => $subtotal,
            'pagado_acumulado' => $subtotal,
        ]);

        \App\Models\PedidoDetalle::whereIn('id', $detalles->pluck('id'))
            ->update(['pagado' => true, 'caja_detalle_id' => $caja->id]);

        $sesion = SesionCaja::where('estado', 'abierta')->first();
        if ($sesion) {
            CajaMovimiento::create([
                'sesion_id'    => $sesion->id,
                'usuario_id'   => auth()->id(),
                'tipo'         => 'ingreso',
                'concepto'     => 'Cobro Mesa ' . $mesa->numero . ' (' . $request->metodo_pago . ') - por platos',
                'referencia_id'=> $caja->id,
                'monto'        => $subtotal,
                'observaciones'=> $request->notas ?? null,
            ]);
        }

        foreach ($detalles as $item) {
            $recetas = Receta::with('insumo')
                ->where('menu_producto_id', $item->menu_producto_id)
                ->get();

            foreach ($recetas as $receta) {
                $insumo = $receta->insumo;
                $cantidadTotal = $receta->cantidad * $item->cantidad;
                $stockAnterior = $insumo->stock_actual;
                $stockNuevo = max(0, $stockAnterior - $cantidadTotal);

                $insumo->update(['stock_actual' => $stockNuevo]);

                InsumoMovimiento::create([
                    'insumo_id'      => $insumo->id,
                    'user_id'        => auth()->id(),
                    'tipo'           => 'salida',
                    'cantidad'       => $cantidadTotal,
                    'costo_unitario' => $insumo->precio_promedio,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo'    => $stockNuevo,
                    'motivo'         => 'Venta Mesa ' . $mesa->numero . ' (por platos)',
                ]);
            }
        }

        $platosPendientes = \App\Models\PedidoDetalle::whereIn('pedido_id', $pedidoIds)
            ->where('pagado', false)
            ->where('anulado', false)
            ->count();

        if ($platosPendientes > 0) {
            return redirect()->route('caja-restaurante.show', $mesa)
                ->with('success', "Platos cobrados (S/ " . number_format($subtotal, 2) . "). Faltan {$platosPendientes} plato(s) por pagar.");
        }

        Pedido::whereIn('mesa_id', $this->idsGrupoMesa($mesa))
            ->whereIn('estado', ['enviado', 'listo'])
            ->update(['estado' => 'cerrado', 'caja_restaurante_id' => $caja->id]);

        Mesa::whereIn('id', $this->idsGrupoMesa($mesa))->update(['estado' => 'libre', 'mesa_principal_id' => null]);

        $tipo = $request->tipo_comprobante ?? 'boleta';

        if ($tipo === 'ninguno') {
            return redirect()->route('mesas.index')
                ->with('success', "Mesa {$mesa->numero} cobrada completa.");
        }

        return redirect()->route('comprobantes.crear', $caja)
            ->with('success', "Mesa {$mesa->numero} cobrada completa.")
            ->with('tipo_comprobante', $tipo);
    }

}
