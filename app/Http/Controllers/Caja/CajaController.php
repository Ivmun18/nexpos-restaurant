<?php

namespace App\Http\Controllers\Caja;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\SesionCaja;
use App\Models\CajaMovimiento;
use Illuminate\Http\Request;
use App\Helpers\EmpresaHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CajaController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;
        $caja = Caja::where('empresa_id', $empresaId)->where('activo', true)->first();
        if (!$caja) {
            $caja = Caja::create(['empresa_id' => $empresaId, 'codigo' => 'CAJA01', 'nombre' => 'Caja Principal', 'activo' => true]);
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

        return Inertia::render('Caja/Index', [
            'caja'         => $caja,
            'sesionActiva' => $sesionActiva,
            'historial'    => $historial,
        ]);
    }

    public function abrir(Request $request)
    {
        $request->validate([
            'monto_apertura' => 'required|numeric|min:0',
        ]);

        $empresaId = auth()->user()->empresa_id;
        $caja = Caja::where('empresa_id', $empresaId)->where('activo', true)->first();
        if (!$caja) {
            $caja = Caja::create(['empresa_id' => $empresaId, 'codigo' => 'CAJA01', 'nombre' => 'Caja Principal', 'activo' => true]);
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

        // Registrar movimiento de apertura
        CajaMovimiento::create([
            'sesion_id'  => $sesion->id,
            'usuario_id' => Auth::id(),
            'tipo'       => 'ingreso',
            'concepto'   => 'Apertura de caja',
            'monto'      => $request->monto_apertura,
        ]);

        return back()->with('success', 'Caja abierta correctamente.');
    }

    public function agregarMovimiento(Request $request)
    {
        $request->validate([
            'tipo'    => 'required|in:ingreso,egreso',
            'concepto'=> 'required|max:200',
            'monto'   => 'required|numeric|min:0.01',
        ]);

        $sesion = SesionCaja::where('estado', 'abierta')->first();

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
        $sesion = SesionCaja::where('estado', 'abierta')
            ->with('movimientos')
            ->first();

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
}
