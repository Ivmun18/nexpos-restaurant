<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Models\CajaRestaurante;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReporteTurnoController extends Controller
{
    public function index(Request $request)
    {
        $desde   = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta   = $request->get('hasta', now()->toDateString());
        $user_id = $request->get('user_id', '');
        $tipo    = $request->get('tipo', '');

        $query = Turno::with('user:id,name,rol')
            ->whereBetween('apertura', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->orderBy('apertura', 'desc');

        if ($user_id) $query->where('user_id', $user_id);
        if ($tipo)    $query->where('tipo', $tipo);

        $turnos = $query->get();

        // Métricas generales
        $totalVentas  = $turnos->sum('total_ventas');
        $totalMesas   = $turnos->sum('total_mesas');
        $totalTurnos  = $turnos->count();
        $promedioVentas = $totalTurnos > 0 ? $totalVentas / $totalTurnos : 0;

        // Por mozo
        $porMozo = $turnos->groupBy('user_id')->map(function($g) {
            $user = $g->first()->user;
            $duracionMin = $g->sum(function($t) {
                $fin = $t->cierre ?? now();
                return $t->apertura->diffInMinutes($fin);
            });
            return [
                'nombre'          => $user->name ?? '—',
                'rol'             => $user->rol ?? '—',
                'turnos'          => $g->count(),
                'total_ventas'    => round($g->sum('total_ventas'), 2),
                'total_mesas'     => $g->sum('total_mesas'),
                'duracion_min'    => $duracionMin,
                'promedio_ventas' => $g->count() > 0 ? round($g->sum('total_ventas') / $g->count(), 2) : 0,
            ];
        })->sortByDesc('total_ventas')->values();

        // Por tipo de turno
        $porTipo = $turnos->groupBy('tipo')->map(fn($g) => [
            'turnos'       => $g->count(),
            'total_ventas' => round($g->sum('total_ventas'), 2),
            'total_mesas'  => $g->sum('total_mesas'),
        ]);

        // Mozos para filtro
        $mozos = User::whereIn('rol', ['mozo', 'admin'])
            ->select('id', 'name', 'rol')
            ->orderBy('name')
            ->get();

        return Inertia::render('Reportes/TurnosMozos', [
            'turnos'          => $turnos,
            'resumen'         => [
                'total_ventas'    => round($totalVentas, 2),
                'total_mesas'     => $totalMesas,
                'total_turnos'    => $totalTurnos,
                'promedio_ventas' => round($promedioVentas, 2),
            ],
            'por_mozo'        => $porMozo,
            'por_tipo'        => $porTipo,
            'mozos'           => $mozos,
            'filtros'         => compact('desde', 'hasta', 'user_id', 'tipo'),
        ]);
    }
}
