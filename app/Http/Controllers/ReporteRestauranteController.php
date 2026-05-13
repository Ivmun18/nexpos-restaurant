<?php

namespace App\Http\Controllers;

use App\Models\CajaRestaurante;
use Illuminate\Http\Request;
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

        return Inertia::render('Reportes/VentasRestaurante', [
            'ventas'     => $ventas,
            'resumen'    => [
                'total_ventas'    => round($totalVentas, 2),
                'total_cobrado'   => round($ventas->sum('monto_pagado'), 2),
                'total_vuelto'    => round($ventas->sum('vuelto'), 2),
                'cantidad_ventas' => $cantidadVentas,
                'ticket_promedio' => round($ticketPromedio, 2),
            ],
            'por_metodo' => $porMetodo,
            'por_dia'    => $porDia,
            'top_mozos'  => $topMozos,
            'mozos'      => $mozos,
            'filtros'    => compact('desde', 'hasta', 'metodo', 'mozo_id'),
        ]);
    }
}
