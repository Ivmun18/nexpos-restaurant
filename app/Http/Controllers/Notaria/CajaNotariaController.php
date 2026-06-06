<?php

namespace App\Http\Controllers\Notaria;

use App\Http\Controllers\Controller;
use App\Models\ActoNotarial;
use App\Models\ActoPago;
use App\Models\SesionCaja;
use App\Models\CajaMovimiento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CajaNotariaController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $buscar = request()->get('buscar', '');

        // Expedientes con saldo pendiente
        $query = ActoNotarial::with(['cliente', 'pagos'])
            ->where('empresa_id', $empresaId)
            ->whereIn('estado_pago', ['pendiente', 'parcial'])
            ->where('estado', '!=', 'cancelado');

        if ($buscar) {
            $query->where(function($q) use ($buscar) {
                $q->where('numero_expediente', 'like', "%{$buscar}%")
                  ->orWhere('asunto', 'like', "%{$buscar}%")
                  ->orWhere('partes_intervinientes', 'like', "%{$buscar}%")
                  ->orWhereHas('cliente', function($q2) use ($buscar) {
                      $q2->where('razon_social', 'like', "%{$buscar}%")
                         ->orWhere('numero_documento', 'like', "%{$buscar}%");
                  });
            });
        }

        $pendientes = $query->orderBy('fecha_ingreso', 'desc')
            ->get()
            ->map(fn($a) => [
                'id'                 => $a->id,
                'numero_expediente'  => $a->numero_expediente,
                'tipo_acto'          => $a->tipo_acto,
                'asunto'             => $a->asunto,
                'partes_intervinientes' => $a->partes_intervinientes,
                'monto_cobrar'       => $a->monto_cobrar,
                'monto_pagado'       => $a->monto_pagado,
                'saldo'              => round($a->monto_cobrar - $a->monto_pagado, 2),
                'estado_pago'        => $a->estado_pago,
                'estado'             => $a->estado,
                'fecha_ingreso'      => $a->fecha_ingreso,
                'pagos'              => $a->pagos,
                'cliente'            => $a->cliente ? [
                    'tipo_documento'   => $a->cliente->tipo_documento,
                    'numero_documento' => $a->cliente->numero_documento,
                    'nombre'           => $a->cliente->razon_social,
                ] : null,
            ]);

        $cajaId = \App\Models\Caja::where('empresa_id', $empresaId)->value('id');
        $sesionAbierta = SesionCaja::where('estado', 'abierta')->where('caja_id', $cajaId)->exists();
        $sesionActual  = SesionCaja::with('movimientos')
            ->where('estado', 'abierta')
            ->where('caja_id', $cajaId)
            ->first();

        // Calcular totales directamente desde comprobantes_sunat (fuente de verdad)
        $resumenCaja = null;
        if ($sesionActual) {
            $fechaApertura = $sesionActual->fecha_apertura;

            // Ingresos desde comprobantes aceptados
            $comprobantes = \DB::table('comprobantes_sunat')
                ->where('empresa_id', $empresaId)
                ->whereIn('estado', ['aceptado', 'emitido'])
                ->where('total', '>', 0)
                ->whereDate('fecha_emision', now()->toDateString())
                ->get();

            $ingresos = $comprobantes->sum('total');

            // Egresos desde movimientos manuales
            $movimientos = $sesionActual->movimientos;
            $egresos = $movimientos->where('tipo', 'egreso')->sum('monto');

            // Desglose por tipo de cobro
            $cobrosExpediente = $comprobantes->whereNotNull('acto_id')->sum('total');
            $serviciosRapidos = $comprobantes->whereNull('acto_id')->sum('total');

            // Desglose por método de pago (desde movimientos)
            $porMetodo = $movimientos->where('tipo', 'ingreso')
                ->filter(fn($m) => !str_contains($m->concepto, 'Apertura'))
                ->groupBy('metodo_pago')
                ->map(fn($g) => round($g->sum('monto'), 2));

            $resumenCaja = [
                'por_metodo'          => $porMetodo,
                'cobros_expediente'   => round($cobrosExpediente, 2),
                'ventas_directas'     => 0,
                'servicios_rapidos'   => round($serviciosRapidos, 2),
                'id'             => $sesionActual->id,
                'apertura'       => $sesionActual->created_at,
                'fondo_inicial'  => $sesionActual->monto_apertura,
                'ingresos'       => round($ingresos, 2),
                'egresos'        => round($egresos, 2),
                'saldo_sistema'  => round($sesionActual->monto_apertura + $ingresos - $egresos, 2),
            ];
        }

        // Expedientes pagados hoy para emitir comprobante
        $pagadosHoy = ActoNotarial::with(['cliente', 'pagos'])
            ->where('empresa_id', $empresaId)
            ->where('estado_pago', 'pagado')
            ->whereDate('updated_at', today())
            ->whereDoesntHave('comprobantes', function($q) {
                $q->whereIn('estado', ['aceptado', 'emitido']);
            })
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn($a) => [
                'id'                    => $a->id,
                'numero_expediente'     => $a->numero_expediente,
                'tipo_acto'             => $a->tipo_acto,
                'asunto'                => $a->asunto,
                'partes_intervinientes' => $a->partes_intervinientes,
                'monto_cobrar'          => $a->monto_cobrar,
                'monto_pagado'          => $a->monto_pagado,
                'saldo'                 => 0,
                'estado_pago'           => $a->estado_pago,
            ]);

        // Historial de cierres de caja
        $historialCierres = \DB::table('sesiones_caja as s')
            ->join('caja as c', 's.caja_id', '=', 'c.id')
            ->where('c.empresa_id', $empresaId)
            ->where('s.estado', 'cerrada')
            ->orderByDesc('s.fecha_cierre')
            ->limit(10)
            ->select('s.fecha_cierre', 's.monto_cierre_sistema', 's.monto_cierre_real', 's.observaciones')
            ->get();

        return Inertia::render('Notaria/Caja/Index', [
            'pendientes'      => $pendientes,
            'pagadosHoy'      => $pagadosHoy,
            'sesionAbierta'   => $sesionAbierta,
            'resumenCaja'     => $resumenCaja,
            'filtros'         => ['buscar' => $buscar],
            'historialCierres'=> $historialCierres,
            'serviciosNotaria' => \App\Models\NotariaServicio::where('empresa_id', $empresaId)->where('activo', true)->orderBy('nombre')->get(['id','nombre','precio']),
        ]);
    }

    public function cobrar(Request $request, ActoNotarial $acto)
    {
        \Log::info("cobrar llamado - acto: " . $acto->id . " tipo_comp: " . $request->tipo_comprobante . " cliente: " . $request->cliente_nombre);
        // Verificar que hay caja abierta
        if (!SesionCaja::join('caja', 'sesiones_caja.caja_id', '=', 'caja.id')->where('sesiones_caja.estado', 'abierta')->where('caja.empresa_id', auth()->user()->empresa->id)->exists()) {
            return back()->with('error', 'Debe abrir la caja antes de registrar cobros.');
        }

        $request->validate([
            'monto'      => 'required|numeric|min:0.01',
            'metodo_pago'=> 'required|in:efectivo,yape,plin,tarjeta,transferencia',
            'tipo'       => 'required|in:adelanto,pago_parcial,pago_final',
            'referencia' => 'nullable|string|max:100',
        ]);

        $nuevoPagado = round($acto->monto_pagado + $request->monto, 2);
        $estadoPago  = $nuevoPagado >= $acto->monto_cobrar ? 'pagado' : 'parcial';

        // Registrar pago detallado
        ActoPago::create([
            'acto_id'      => $acto->id,
            'usuario_id'   => auth()->id(),
            'monto'        => $request->monto,
            'metodo_pago'  => $request->metodo_pago,
            'tipo'         => $request->tipo,
            'referencia'   => $request->referencia,
            'observaciones'=> $request->observaciones,
        ]);

        // Actualizar monto pagado en el acto
        $acto->update([
            'monto_pagado' => $nuevoPagado,
            'estado_pago'  => $estadoPago,
        ]);

        // Si pago final, marcar como finalizado
        if ($estadoPago === 'pagado' && $acto->estado === 'en_proceso') {
            $acto->update(['estado' => 'finalizado']);
        }

        // Registrar en caja si hay sesión abierta
        $sesion = SesionCaja::join('caja', 'sesiones_caja.caja_id', '=', 'caja.id')->where('sesiones_caja.estado', 'abierta')->where('caja.empresa_id', auth()->user()->empresa->id)->select('sesiones_caja.*')->first();
        if ($sesion) {
            CajaMovimiento::create([
                'sesion_id'    => $sesion->id,
                'usuario_id'   => auth()->id(),
                'tipo'         => 'ingreso',
                'concepto'     => ucfirst($request->tipo) . ' ' . $acto->numero_expediente . ' (' . $request->metodo_pago . ')',
                'referencia_id'=> $acto->id,
                'monto'        => $request->monto,
                'metodo_pago'  => $request->metodo_pago,
            ]);
        }

        // Solo emitir comprobante cuando el expediente está TOTALMENTE pagado
        if ($estadoPago === 'pagado' && $request->tipo_comprobante && $request->cliente_nombre) {
            $resultado = app(\App\Http\Controllers\Notaria\ComprobantesNotariaController::class)->emitir($request, $acto);
            $data = $resultado->getData(true);
            return response()->json([
                'success' => true,
                'mensaje' => 'Pago registrado. ' . ($data['mensaje'] ?? ''),
                'pdf'     => $data['pdf'] ?? null,
                'emitido' => $data['success'] ?? false,
            ]);
        }

        $saldoPendiente = round($acto->monto_cobrar - $nuevoPagado, 2);
        $ultimoPago = $acto->pagos()->latest()->first();
        $msg = $estadoPago === 'pagado'
            ? 'Pago de S/ ' . $request->monto . ' registrado. Expediente cancelado.'
            : 'Adelanto de S/ ' . $request->monto . ' registrado. Saldo pendiente: S/ ' . $saldoPendiente;

        return response()->json([
            'success' => true,
            'mensaje' => $msg,
            'pdf'     => $ultimoPago ? '/notaria/recibo/' . $acto->id . '/' . $ultimoPago->id : null,
        ]);
    }

    public function servicioRapido(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'tipo_servicio'    => 'nullable|string',
            'monto'            => 'required|numeric|min:0.01',
            'metodo_pago'      => 'required|in:efectivo,yape,plin,tarjeta,transferencia',
            'cliente_nombre'   => 'nullable|string',
            'cliente_documento'=> 'nullable|string',
        ]);

        if (!SesionCaja::join('caja', 'sesiones_caja.caja_id', '=', 'caja.id')->where('sesiones_caja.estado', 'abierta')->where('caja.empresa_id', auth()->user()->empresa->id)->exists()) {
            return response()->json(['success' => false, 'mensaje' => 'No hay caja abierta.']);
        }

        $requestEmitir = new \Illuminate\Http\Request();
        $requestEmitir->merge([
            'skip_caja_registro'       => true,
            'tipo_comprobante'         => (strlen($request->cliente_documento ?? '') === 11) ? '01' : '03',
            'cliente_tipo_documento'   => strlen($request->cliente_documento ?? '') == 11 ? '6' : '1',
            'cliente_numero_documento' => $request->cliente_documento ?? '00000000',
            'cliente_nombre'           => $request->cliente_nombre ?? 'CLIENTES VARIOS',
            'cliente_email'            => '',
            'metodo_pago'              => $request->metodo_pago,
            'items' => collect($request->items ?? [])->map(fn($it) => [
                'descripcion' => $it['tipo_servicio'] ?? 'Servicio notarial',
                'cantidad'    => $it['cantidad'] ?? 1,
                'precio'      => round(($it['precio_unitario'] ?? 0), 4),
            ])->toArray(),
        ]);

        $resultado = app(\App\Http\Controllers\Notaria\ComprobantesNotariaController::class)->ventaDirecta($requestEmitir);
        $data = $resultado->getData(true);

        if ($data['success'] ?? false) {
            $sesion = SesionCaja::join('caja', 'sesiones_caja.caja_id', '=', 'caja.id')->where('sesiones_caja.estado', 'abierta')->where('caja.empresa_id', auth()->user()->empresa->id)->select('sesiones_caja.*')->first();
            if ($sesion) {
                \App\Models\CajaMovimiento::create([
                    'sesion_id'   => $sesion->id,
                    'usuario_id'  => auth()->id(),
                    'tipo'        => 'ingreso',
                    'concepto'    => 'Servicio rapido: ' . $request->tipo_servicio . ' (' . $request->metodo_pago . ')',
                    'monto'       => $request->monto,
                    'metodo_pago' => $request->metodo_pago,
                ]);
            }
        }

        return response()->json($data);
    }

    public function abrir(\Illuminate\Http\Request $request)
    {
        $request->validate(['monto_apertura' => 'required|numeric|min:0']);

        $caja = \DB::table('caja')->where('empresa_id', auth()->user()->empresa_id)->first();
        $cajaId = $caja ? $caja->id : 1;

        if (SesionCaja::where('estado', 'abierta')->where('caja_id', $cajaId)->exists()) {
            return back()->with('error', 'Ya hay una caja abierta.');
        }

        $sesion = SesionCaja::create([
            'caja_id'        => $cajaId,
            'usuario_id'     => auth()->id(),
            'monto_apertura' => $request->monto_apertura,
            'estado'         => 'abierta',
        ]);

        CajaMovimiento::create([
            'sesion_id'  => $sesion->id,
            'usuario_id' => auth()->id(),
            'tipo'       => 'ingreso',
            'concepto'   => 'Apertura de caja',
            'monto'      => $request->monto_apertura,
        ]);

        return back()->with('success', 'Caja abierta con S/ ' . $request->monto_apertura);
    }

    public function cerrar(\Illuminate\Http\Request $request)
    {
        $sesion = SesionCaja::join('caja', 'sesiones_caja.caja_id', '=', 'caja.id')->where('sesiones_caja.estado', 'abierta')->where('caja.empresa_id', auth()->user()->empresa->id)->with('movimientos')->select('sesiones_caja.*')->first();

        if (!$sesion) {
            return back()->with('error', 'No hay caja abierta.');
        }

        $ingresos     = $sesion->movimientos->where('tipo', 'ingreso')->sum('monto');
        $egresos      = $sesion->movimientos->where('tipo', 'egreso')->sum('monto');
        $montoSistema = round($sesion->monto_apertura + $ingresos - $egresos, 2);
        $montoReal    = $request->monto_real ?? $montoSistema;
        $diferencia   = round($montoReal - $montoSistema, 2);

        $sesion->update([
            'fecha_cierre'         => now(),
            'monto_cierre_sistema' => $montoSistema,
            'monto_cierre_real'    => $montoReal,
            'diferencia'           => $diferencia,
            'estado'               => 'cerrada',
            'observaciones'        => $request->observaciones,
        ]);

        return back()->with('success', 'Caja cerrada. Saldo sistema: S/ ' . $montoSistema);
    }
}
