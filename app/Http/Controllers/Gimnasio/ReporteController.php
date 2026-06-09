<?php

namespace App\Http\Controllers\Gimnasio;

use App\Http\Controllers\Controller;
use App\Models\Gimnasio\GimnasioPago;
use App\Models\Gimnasio\GimnasioMiembro;
use App\Models\Gimnasio\GimnasioPlan;
use App\Models\Gimnasio\GimnasioAcceso;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $desde = $request->get('desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', Carbon::now()->format('Y-m-d'));

        $pagos = GimnasioPago::where('empresa_id', $empresa_id)
            ->whereBetween('fecha_pago', [$desde, $hasta])
            ->where('estado', 'pagado')
            ->with('miembro', 'plan')
            ->get();

        // Totales generales
        $totalIngresos  = $pagos->sum('monto');
        $totalPagos     = $pagos->count();
        $ticketPromedio = $totalPagos > 0 ? round($totalIngresos / $totalPagos, 2) : 0;

        // Por plan
        $porPlan = $pagos->groupBy('plan_id')->map(fn($p) => [
            'plan'    => $p->first()->plan?->nombre ?? 'Sin plan',
            'total'   => $p->sum('monto'),
            'count'   => $p->count(),
            'precio'  => $p->first()->plan?->precio ?? 0,
        ])->values()->sortByDesc('total')->values();

        // Por método de pago
        $porMetodo = $pagos->groupBy('metodo_pago')->map(fn($p) => [
            'metodo' => $p->first()->metodo_pago,
            'total'  => $p->sum('monto'),
            'count'  => $p->count(),
        ])->values()->sortByDesc('total')->values();

        // Por día en el período
        $porDia = $pagos->groupBy(fn($p) => Carbon::parse($p->fecha_pago)->format('Y-m-d'))
            ->map(fn($p, $fecha) => [
                'fecha'  => Carbon::parse($fecha)->toDateString(),
                'total'  => $p->sum('monto'),
                'count'  => $p->count(),
            ])->sortKeys()->values();

        // Ingresos por mes (últimos 6)
        $porMes = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $ing = GimnasioPago::where('empresa_id', $empresa_id)
                ->whereYear('fecha_pago', $mes->year)
                ->whereMonth('fecha_pago', $mes->month)
                ->where('estado', 'pagado')->sum('monto');
            $porMes[] = ['mes' => $mes->locale('es')->isoFormat('MMM YY'), 'total' => $ing];
        }

        // Miembros nuevos en período
        $miembrosNuevos = GimnasioMiembro::where('empresa_id', $empresa_id)
            ->whereBetween('created_at', [$desde.' 00:00:00', $hasta.' 23:59:59'])
            ->count();

        // Accesos en período
        $totalAccesos = GimnasioAcceso::where('empresa_id', $empresa_id)
            ->whereBetween('entrada', [$desde.' 00:00:00', $hasta.' 23:59:59'])
            ->count();

        // Pagos por sesión (sin plan)
        $pagosSesion = $pagos->whereNull('plan_id');
        $ingresosSesion = $pagosSesion->sum('monto');
        $ingresosMembresia = $pagos->whereNotNull('plan_id')->sum('monto');

        // Resumen hoy / semana / mes
        $hoy = Carbon::today();
        $ingresosHoy     = GimnasioPago::where('empresa_id', $empresa_id)->whereDate('fecha_pago', $hoy)->where('estado','pagado')->sum('monto');
        $ingresosSemana  = GimnasioPago::where('empresa_id', $empresa_id)->whereBetween('fecha_pago', [$hoy->copy()->startOfWeek(), $hoy->copy()->endOfWeek()])->where('estado','pagado')->sum('monto');
        $ingresosMesAct  = GimnasioPago::where('empresa_id', $empresa_id)->whereMonth('fecha_pago', $hoy->month)->whereYear('fecha_pago', $hoy->year)->where('estado','pagado')->sum('monto');

        return Inertia::render('Gimnasio/Reportes/Index', compact(
            'desde', 'hasta', 'totalIngresos', 'totalPagos', 'ticketPromedio',
            'porPlan', 'porMetodo', 'porDia', 'porMes',
            'miembrosNuevos', 'totalAccesos',
            'ingresosSesion', 'ingresosMembresia',
            'ingresosHoy', 'ingresosSemana', 'ingresosMesAct'
        ));
    }
}
