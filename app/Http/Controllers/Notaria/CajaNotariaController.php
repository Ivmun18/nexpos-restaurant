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
            ]);

        $sesionAbierta = SesionCaja::where('estado', 'abierta')->exists();
        $sesionActual  = SesionCaja::with('movimientos')
            ->where('estado', 'abierta')
            ->first();

        // Calcular totales de la sesión actual
        $resumenCaja = null;
        if ($sesionActual) {
            $ingresos = $sesionActual->movimientos->where('tipo', 'ingreso')->sum('monto');
            $egresos  = $sesionActual->movimientos->where('tipo', 'egreso')->sum('monto');
            $resumenCaja = [
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

        return Inertia::render('Notaria/Caja/Index', [
            'pendientes'    => $pendientes,
            'pagadosHoy'    => $pagadosHoy,
            'sesionAbierta' => $sesionAbierta,
            'resumenCaja'   => $resumenCaja,
            'filtros'       => ['buscar' => $buscar],
        ]);
    }

    public function cobrar(Request $request, ActoNotarial $acto)
    {
        // Verificar que hay caja abierta
        if (!SesionCaja::where('estado', 'abierta')->exists()) {
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
        $sesion = SesionCaja::where('estado', 'abierta')->first();
        if ($sesion) {
            CajaMovimiento::create([
                'sesion_id'    => $sesion->id,
                'usuario_id'   => auth()->id(),
                'tipo'         => 'ingreso',
                'concepto'     => ucfirst($request->tipo) . ' ' . $acto->numero_expediente . ' (' . $request->metodo_pago . ')',
                'referencia_id'=> $acto->id,
                'monto'        => $request->monto,
            ]);
        }

        return back()->with('success', 'Pago de S/ ' . $request->monto . ' registrado correctamente.');
    }

    public function abrir(\Illuminate\Http\Request $request)
    {
        $request->validate(['monto_apertura' => 'required|numeric|min:0']);

        if (SesionCaja::where('estado', 'abierta')->exists()) {
            return back()->with('error', 'Ya hay una caja abierta.');
        }

        $sesion = SesionCaja::create([
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
        $sesion = SesionCaja::where('estado', 'abierta')->with('movimientos')->first();

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
