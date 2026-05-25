<?php

namespace App\Http\Controllers;

use App\Models\CajaRestaurante;
use App\Models\Compra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReporteRestauranteController extends Controller
{
    public function index(Request $request)
    {
        $desde   = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta   = $request->get('hasta', now()->toDateString());
        $metodo  = $request->get('metodo', '');
        $mozo_id = $request->get('mozo_id', '');

        $query = CajaRestaurante::with(['mesa:id,numero,nombre,zona', 'user:id,name,rol'])
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->orderBy('created_at', 'desc');

        if ($metodo)  $query->where('metodo_pago', $metodo);
        if ($mozo_id) $query->where('user_id', $mozo_id);

        $ventas = $query->get();

        $totalVentas    = $ventas->sum('total');
        $cantidadVentas = $ventas->count();
        $ticketPromedio = $cantidadVentas > 0 ? $totalVentas / $cantidadVentas : 0;

        $porMetodo = $ventas->groupBy('metodo_pago')->map(fn($g) => [
            'cantidad' => $g->count(),
            'total'    => round($g->sum('total'), 2),
        ]);

        $porComprobante = $ventas->groupBy(fn($v) => $v->tipo_comprobante ?? 'ninguno')->map(fn($g) => [
            'cantidad' => $g->count(),
            'total'    => round($g->sum('total'), 2),
        ]);

        $porDia = $ventas->groupBy(fn($v) => $v->created_at->toDateString())
            ->map(fn($g) => [
                'fecha'    => $g->first()->created_at->locale('es')->isoFormat('ddd D MMM'),
                'total'    => round($g->sum('total'), 2),
                'cantidad' => $g->count(),
            ])->values();

        $topMozos = $ventas->groupBy('user_id')->map(fn($g) => [
            'nombre'   => optional($g->first()->user)->name ?? '—',
            'cantidad' => $g->count(),
            'total'    => round($g->sum('total'), 2),
        ])->sortByDesc('total')->values();

        $mozos = \App\Models\User::whereIn('rol', ['mozo', 'admin'])
            ->select('id', 'name', 'rol')
            ->orderBy('name')
            ->get();

        // ===== GANANCIAS (solo admin) =====
        $esAdmin = auth()->user()->rol === 'admin';
        $ganancias = null;
        if ($esAdmin) {
            $vista = $request->get('vista_ganancia', 'dia');
            $ini = $desde . ' 00:00:00';
            $fin = $hasta . ' 23:59:59';

            if ($vista === 'semana') {
                $fmtV = "DATE_FORMAT(created_at, '%x-S%v')";
                $fmtG = "DATE_FORMAT(fecha_emision, '%x-S%v')";
            } elseif ($vista === 'mes') {
                $fmtV = "DATE_FORMAT(created_at, '%Y-%m')";
                $fmtG = "DATE_FORMAT(fecha_emision, '%Y-%m')";
            } else {
                $fmtV = "DATE(created_at)";
                $fmtG = "DATE(fecha_emision)";
            }

            $gv = CajaRestaurante::whereBetween('created_at', [$ini, $fin])
                ->select(DB::raw("$fmtV as periodo"), DB::raw("SUM(total) as total"))
                ->groupBy('periodo')->pluck('total', 'periodo');

            $gg = Compra::whereBetween('fecha_emision', [$ini, $fin])
                ->where('estado', '!=', 'anulado')
                ->select(DB::raw("$fmtG as periodo"), DB::raw("SUM(total) as total"))
                ->groupBy('periodo')->pluck('total', 'periodo');

            $filas = collect($gv->keys())->merge($gg->keys())->unique()->sortDesc()->values()
                ->map(function ($p) use ($gv, $gg) {
                    $vv = (float) ($gv[$p] ?? 0);
                    $gg2 = (float) ($gg[$p] ?? 0);
                    return ['periodo' => $p, 'ventas' => round($vv, 2), 'gastos' => round($gg2, 2), 'ganancia' => round($vv - $gg2, 2)];
                });

            $totV = (float) CajaRestaurante::whereBetween('created_at', [$ini, $fin])->sum('total');
            $totG = (float) Compra::whereBetween('fecha_emision', [$ini, $fin])->where('estado', '!=', 'anulado')->sum('total');

            $ganancias = [
                'vista'   => $vista,
                'resumen' => ['ventas' => round($totV, 2), 'gastos' => round($totG, 2), 'ganancia' => round($totV - $totG, 2)],
                'filas'   => $filas,
            ];
        }

        return Inertia::render('Reportes/VentasRestaurante', [
            'ventas'     => $ventas,
            'resumen'    => [
                'total_ventas'    => round($totalVentas, 2),
                'total_cobrado'   => round($ventas->sum('monto_pagado'), 2),
                'total_vuelto'    => round($ventas->sum('vuelto'), 2),
                'cantidad_ventas' => $cantidadVentas,
                'ticket_promedio' => round($ticketPromedio, 2),
            ],
            'por_metodo'      => $porMetodo,
            'por_comprobante' => $porComprobante,
            'por_dia'    => $porDia,
            'top_mozos'  => $topMozos,
            'mozos'      => $mozos,
            'filtros'    => compact('desde', 'hasta', 'metodo', 'mozo_id'),
            'es_admin'   => $esAdmin,
            'ganancias'  => $ganancias,
        ]);
    }
}
