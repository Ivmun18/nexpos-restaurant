<?php

namespace App\Http\Controllers\Caja;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\SesionCaja;
use App\Models\CajaMovimiento;
use Illuminate\Http\Request;
use App\Helpers\EmpresaHelper;
use App\Helpers\SucursalHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CajaController extends Controller
{
    public function index()
    {
        $empresaId  = auth()->user()->empresa_id;
        $sucursalId = SucursalHelper::id();
        $caja = Caja::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->when($sucursalId !== null, fn($q) => $q->where('sucursal_id', $sucursalId), fn($q) => $q->whereNull('sucursal_id'))
            ->first();
        if (!$caja) {
            $caja = Caja::create(['empresa_id' => $empresaId, 'sucursal_id' => $sucursalId, 'codigo' => 'CAJA01', 'nombre' => 'Caja Principal', 'activo' => true]);
        }
        $sesionActiva = SesionCaja::where('caja_id', $caja->id)
            ->where('estado', 'abierta')
            ->with('movimientos', 'usuario')
            ->first();

        $historial = SesionCaja::where('caja_id', $caja->id)
            ->where('estado', 'cerrada')
            ->with('usuario')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $desglosePagos = $sesionActiva
            ? CajaMovimiento::where('caja_movimientos.sesion_id', $sesionActiva->id)
                ->where('caja_movimientos.tipo', 'ingreso')
                ->join('caja_restaurante', 'caja_restaurante.id', '=', 'caja_movimientos.referencia_id')
                ->selectRaw('caja_restaurante.metodo_pago, SUM(caja_movimientos.monto) as total, COUNT(*) as cantidad')
                ->groupBy('caja_restaurante.metodo_pago')
                ->get()
            : collect();

        return Inertia::render('Caja/Index', [
            'caja'          => $caja,
            'sesionActiva'  => $sesionActiva,
            'historial'     => $historial,
            'desglosePagos' => $desglosePagos,
        ]);
    }

    public function abrir(Request $request)
    {
        $request->validate([
            'monto_apertura' => 'required|numeric|min:0',
        ]);

        $empresaId  = auth()->user()->empresa_id;
        $sucursalId = SucursalHelper::id();
        $caja = Caja::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->when($sucursalId !== null, fn($q) => $q->where('sucursal_id', $sucursalId), fn($q) => $q->whereNull('sucursal_id'))
            ->first();
        if (!$caja) {
            $caja = Caja::create(['empresa_id' => $empresaId, 'sucursal_id' => $sucursalId, 'codigo' => 'CAJA01', 'nombre' => 'Caja Principal', 'activo' => true]);
        }

        // Verificar que no haya sesión abierta
        $sesionExistente = SesionCaja::where('caja_id', $caja->id)
            ->where('estado', 'abierta')
            ->first();

        if ($sesionExistente) {
            return back()->with('error', 'Ya hay una sesión de caja abierta.');
        }

        $sesion = SesionCaja::create([
            'caja_id'        => $caja->id,
            'usuario_id'     => Auth::id(),
            'fecha_apertura' => now(),
            'monto_apertura' => $request->monto_apertura,
            'estado'         => 'abierta',
            'observaciones'  => $request->observaciones,
        ]);

        // El monto de apertura ya queda en sesion->monto_apertura, no se registra como movimiento

        return back()->with('success', 'Caja abierta correctamente.');
    }

    public function agregarMovimiento(Request $request)
    {
        $request->validate([
            'tipo'    => 'required|in:ingreso,egreso',
            'concepto'=> 'required|max:200',
            'monto'   => 'required|numeric|min:0.01',
        ]);

        $empresaId  = auth()->user()->empresa_id;
        $sucursalId = SucursalHelper::id();
        $caja = Caja::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->when($sucursalId !== null, fn($q) => $q->where('sucursal_id', $sucursalId), fn($q) => $q->whereNull('sucursal_id'))
            ->first();

        $sesion = $caja
            ? SesionCaja::where('caja_id', $caja->id)->where('estado', 'abierta')->first()
            : null;

        if (!$sesion) {
            return back()->with('error', 'No hay sesión de caja abierta.');
        }

        CajaMovimiento::create([
            'sesion_id'    => $sesion->id,
            'usuario_id'   => Auth::id(),
            'tipo'         => $request->tipo,
            'concepto'     => $request->concepto,
            'monto'        => $request->monto,
            'observaciones'=> $request->observaciones,
        ]);

        return back()->with('success', 'Movimiento registrado correctamente.');
    }

    public function cerrar(Request $request)
    {
        $empresaId  = auth()->user()->empresa_id;
        $sucursalId = SucursalHelper::id();
        $caja = Caja::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->when($sucursalId !== null, fn($q) => $q->where('sucursal_id', $sucursalId), fn($q) => $q->whereNull('sucursal_id'))
            ->first();

        $sesion = $caja
            ? SesionCaja::where('caja_id', $caja->id)->where('estado', 'abierta')->with('movimientos')->first()
            : null;

        if (!$sesion) {
            return back()->with('error', 'No hay sesión de caja abierta.');
        }

        $totalIngresos = $sesion->movimientos->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos  = $sesion->movimientos->where('tipo', 'egreso')->sum('monto');
        $montoSistema  = $sesion->monto_apertura + $totalIngresos - $totalEgresos;
        $montoReal     = $request->monto_cierre_real ?? $montoSistema;
        $diferencia    = $montoReal - $montoSistema;

        $sesion->update([
            'fecha_cierre'         => now(),
            'monto_cierre_sistema' => round($montoSistema, 2),
            'monto_cierre_real'    => round($montoReal, 2),
            'diferencia'           => round($diferencia, 2),
            'estado'               => 'cerrada',
            'observaciones'        => $request->observaciones,
        ]);

        return back()->with('success', 'Caja cerrada correctamente.');
    }

    public function corregirSesion(Request $request)
    {
        $request->validate([
            'sesion_id'     => 'required|exists:sesiones_caja,id',
            'monto_real'    => 'required|numeric|min:0',
            'observaciones' => 'required|string|max:500',
        ]);

        $sesion     = \App\Models\SesionCaja::findOrFail($request->sesion_id);
        $diferencia = round($request->monto_real - $sesion->monto_cierre_sistema, 2);

        $sesion->update([
            'monto_cierre_real' => round($request->monto_real, 2),
            'diferencia'        => $diferencia,
            'observaciones'     => ($sesion->observaciones ?? '') . ' | CORRECCIÓN Admin: ' . $request->observaciones,
        ]);

        return back()->with('success', 'Arqueo corregido correctamente.');
    }
}
