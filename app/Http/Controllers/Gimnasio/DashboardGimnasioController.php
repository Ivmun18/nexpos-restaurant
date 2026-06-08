<?php

namespace App\Http\Controllers\Gimnasio;

use App\Http\Controllers\Controller;
use App\Models\Gimnasio\GimnasioMiembro;
use App\Models\Gimnasio\GimnasioPago;
use App\Models\Gimnasio\GimnasioAcceso;
use App\Models\Gimnasio\GimnasioPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardGimnasioController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;
        $hoy = Carbon::today();

        $stats = [
            'total_miembros' => GimnasioMiembro::where('empresa_id', $empresa_id)->where('activo', true)->count(),
            'activos'        => GimnasioMiembro::where('empresa_id', $empresa_id)->where('estado', 'activo')->count(),
            'vencidos'       => GimnasioMiembro::where('empresa_id', $empresa_id)->where('estado', 'vencido')->count(),
            'por_vencer'     => GimnasioMiembro::where('empresa_id', $empresa_id)
                                    ->where('estado', 'activo')
                                    ->whereBetween('membrecia_vencimiento', [$hoy, $hoy->copy()->addDays(7)])
                                    ->count(),
            'ingresos_mes'   => GimnasioPago::where('empresa_id', $empresa_id)
                                    ->whereMonth('fecha_pago', $hoy->month)
                                    ->whereYear('fecha_pago', $hoy->year)
                                    ->where('estado', 'pagado')->sum('monto'),
            'ingresos_hoy'   => GimnasioPago::where('empresa_id', $empresa_id)
                                    ->whereDate('fecha_pago', $hoy)
                                    ->where('estado', 'pagado')->sum('monto'),
            'accesos_hoy'    => GimnasioAcceso::where('empresa_id', $empresa_id)
                                    ->whereDate('entrada', $hoy)->count(),
            'dentro_ahora'   => GimnasioAcceso::where('empresa_id', $empresa_id)
                                    ->whereDate('entrada', $hoy)
                                    ->whereNull('salida')->count(),
        ];

        $vencen_pronto = GimnasioMiembro::where('empresa_id', $empresa_id)
            ->where('estado', 'activo')
            ->whereBetween('membrecia_vencimiento', [$hoy, $hoy->copy()->addDays(7)])
            ->with('plan')->orderBy('membrecia_vencimiento')->get();

        // Ingresos últimos 6 meses
        $ingresosPorMes = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $ingresosPorMes[] = [
                'mes'      => $mes->locale('es')->isoFormat('MMM YY'),
                'ingresos' => GimnasioPago::where('empresa_id', $empresa_id)
                                ->whereYear('fecha_pago', $mes->year)
                                ->whereMonth('fecha_pago', $mes->month)
                                ->where('estado', 'pagado')->sum('monto'),
            ];
        }

        // Accesos por día (últimos 7 días)
        $accesosPorDia = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = Carbon::today()->subDays($i);
            $accesosPorDia[] = [
                'dia'     => $dia->locale('es')->isoFormat('ddd D'),
                'accesos' => GimnasioAcceso::where('empresa_id', $empresa_id)
                                ->whereDate('entrada', $dia)->count(),
            ];
        }

        // Ingresos por plan (mes actual)
        $ingresosPorPlan = GimnasioPago::where('empresa_id', $empresa_id)
            ->whereMonth('fecha_pago', $hoy->month)
            ->whereYear('fecha_pago', $hoy->year)
            ->where('estado', 'pagado')
            ->with('plan')
            ->get()
            ->groupBy('plan_id')
            ->map(fn($pagos) => [
                'plan'   => $pagos->first()->plan?->nombre ?? 'Sin plan',
                'total'  => $pagos->sum('monto'),
                'count'  => $pagos->count(),
            ])->values();

        // Últimos accesos de hoy
        $ultimosAccesos = GimnasioAcceso::where('empresa_id', $empresa_id)
            ->whereDate('entrada', $hoy)
            ->with('miembro')
            ->orderByDesc('entrada')
            ->limit(8)->get();

        return Inertia::render('Gimnasio/Dashboard', compact(
            'stats', 'vencen_pronto', 'ingresosPorMes',
            'accesosPorDia', 'ingresosPorPlan', 'ultimosAccesos'
        ));
    }
}
