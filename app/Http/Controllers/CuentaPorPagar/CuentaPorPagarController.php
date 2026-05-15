<?php

namespace App\Http\Controllers\CuentaPorPagar;

use App\Helpers\EmpresaHelper;
use App\Http\Controllers\Controller;
use App\Models\CuentaPorPagar;
use App\Models\PagoProveedor;
use App\Models\Proveedor;
use App\Models\Compra;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CuentaPorPagarController extends Controller
{
    public function index()
    {
        $cuentas = CuentaPorPagar::with('proveedor')
            ->where('empresa_id', EmpresaHelper::id())
            ->where('estado', '!=', 'pagado')
            ->orderBy('fecha_vencimiento', 'asc')
            ->paginate(20);

        $totales = CuentaPorPagar::where('empresa_id', EmpresaHelper::id())
            ->selectRaw('estado, SUM(monto_pendiente) as total')
            ->groupBy('estado')
            ->get()
            ->keyBy('estado');

        return Inertia::render('CuentasPorPagar/Index', [
            'cuentas' => $cuentas,
            'totales' => $totales,
        ]);
    }

    public function show(CuentaPorPagar $cuenta)
    {
        $this->authorize('view', $cuenta);
        $cuenta->load('proveedor', 'compra', 'pagos.usuario');
        
        return Inertia::render('CuentasPorPagar/Show', [
            'cuenta' => $cuenta,
        ]);
    }

    public function registrarPago(Request $request, CuentaPorPagar $cuenta)
    {
        $this->authorize('update', $cuenta);

        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'forma_pago' => 'required|in:efectivo,transferencia,cheque,tarjeta',
            'numero_comprobante' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $cuenta) {
            $montoAnterior = $cuenta->monto_pagado;
            $nuevoMonto = $cuenta->monto_pagado + $request->monto;

            // Crear registro de pago
            $pago = PagoProveedor::create([
                'empresa_id' => EmpresaHelper::id(),
                'cuenta_por_pagar_id' => $cuenta->id,
                'usuario_id' => Auth::id(),
                'monto' => $request->monto,
                'fecha_pago' => $request->fecha_pago,
                'numero_comprobante' => $request->numero_comprobante,
                'forma_pago' => $request->forma_pago,
                'observaciones' => $request->observaciones,
            ]);

            // Actualizar cuenta por pagar
            $nuevoEstado = $nuevoMonto >= $cuenta->monto_total ? 'pagado' : 'parcial';
            $cuenta->update([
                'monto_pagado' => $nuevoMonto,
                'monto_pendiente' => $cuenta->monto_total - $nuevoMonto,
                'estado' => $nuevoEstado,
            ]);

            // Auditoría
            AuditService::registrarPago($pago);
        });

        return redirect()->route('cuentas-por-pagar.show', $cuenta)->with('success', 'Pago registrado correctamente.');
    }
}
