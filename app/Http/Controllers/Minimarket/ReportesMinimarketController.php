<?php

namespace App\Http\Controllers\Minimarket;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportesMinimarketController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $desde = $request->desde ?? now()->startOfMonth()->toDateString();
        $hasta = $request->hasta ?? now()->toDateString();

        // Ventas del período
        $ventas = Venta::where('empresa_id', $empresaId)
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->where('estado', '!=', 'anulado')
            ->get();

        $totalPeriodo   = $ventas->sum('total');
        $totalEfectivo  = $ventas->where('metodo_pago', 'efectivo')->sum('total');
        $totalYape      = $ventas->where('metodo_pago', 'yape')->sum('total');
        $totalPlin      = $ventas->where('metodo_pago', 'plin')->sum('total');
        $totalTarjeta   = $ventas->where('metodo_pago', 'tarjeta')->sum('total');

        // Ventas por día
        $ventasPorDia = Venta::where('empresa_id', $empresaId)
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->where('estado', '!=', 'anulado')
            ->selectRaw('DATE(fecha_emision) as fecha, COUNT(*) as cantidad, SUM(total) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Top productos
        $topProductos = VentaDetalle::selectRaw('descripcion, SUM(cantidad) as total_cantidad, SUM(total) as total_monto')
            ->whereHas('venta', fn($q) => $q->where('empresa_id', $empresaId)
                ->whereDate('fecha_emision', '>=', $desde)
                ->whereDate('fecha_emision', '<=', $hasta)
                ->where('estado', '!=', 'anulado'))
            ->groupBy('descripcion')
            ->orderByDesc('total_monto')
            ->limit(10)
            ->get();

        // Métodos de pago
        $metodosPago = collect([
            ['metodo_pago' => 'efectivo', 'cantidad' => $ventas->where('metodo_pago','efectivo')->count(), 'total' => $totalEfectivo],
            ['metodo_pago' => 'yape',     'cantidad' => $ventas->where('metodo_pago','yape')->count(),     'total' => $totalYape],
            ['metodo_pago' => 'plin',     'cantidad' => $ventas->where('metodo_pago','plin')->count(),     'total' => $totalPlin],
            ['metodo_pago' => 'tarjeta',  'cantidad' => $ventas->where('metodo_pago','tarjeta')->count(),  'total' => $totalTarjeta],
        ])->filter(fn($m) => $m['total'] > 0)->values();

        // Ventas por vendedor
        $ventasPorVendedor = \App\Models\Venta::where('empresa_id', $empresaId)
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->where('estado', '!=', 'anulado')
            ->with('usuario:id,name,rol')
            ->get()
            ->groupBy('usuario_id')
            ->map(function($ventas, $userId) {
                $usuario = $ventas->first()->usuario;
                return [
                    'usuario_id' => $userId,
                    'nombre'     => $usuario?->name ?? 'Sin usuario',
                    'rol'        => $usuario?->rol ?? '',
                    'cantidad'   => $ventas->count(),
                    'total'      => round($ventas->sum('total'), 2),
                    'efectivo'   => round($ventas->where('metodo_pago','efectivo')->sum('total'), 2),
                    'yape'       => round($ventas->where('metodo_pago','yape')->sum('total'), 2),
                    'plin'       => round($ventas->where('metodo_pago','plin')->sum('total'), 2),
                    'tarjeta'    => round($ventas->where('metodo_pago','tarjeta')->sum('total'), 2),
                ];
            })->values();

        return Inertia::render('Minimarket/Reportes', [
            'resumen' => [
                'total_periodo'  => round($totalPeriodo, 2),
                'total_efectivo' => round($totalEfectivo, 2),
                'total_yape'     => round($totalYape, 2),
                'total_plin'     => round($totalPlin, 2),
                'total_tarjeta'  => round($totalTarjeta, 2),
                'total_ventas'   => $ventas->count(),
                'ticket_promedio'=> $ventas->count() > 0 ? round($totalPeriodo / $ventas->count(), 2) : 0,
            ],
            'ventas_por_dia' => $ventasPorDia,
            'top_productos'  => $topProductos,
            'metodos_pago'        => $metodosPago,
            'ventas_por_vendedor' => $ventasPorVendedor,
            'desde'               => $desde,
            'hasta'               => $hasta,
        ]);
    }
}