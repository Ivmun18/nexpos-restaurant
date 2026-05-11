<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportesFarmaciaController extends Controller
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
            ->selectRaw('DATE(created_at) as fecha, COUNT(*) as cantidad, SUM(total) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Top productos
        $topProductos = VentaDetalle::selectRaw('descripcion, SUM(cantidad) as total_cantidad, SUM(total) as total_monto')
            ->whereHas('venta', fn($q) => $q->where('empresa_id', $empresaId)
                ->whereDate('fecha_emision', '>=', $desde)
                ->whereDate('fecha_emision', '<=', $hasta))
            ->groupBy('descripcion')
            ->orderByDesc('total_monto')
            ->limit(10)
            ->get();

        $metodosPago = collect([
            ['metodo_pago' => 'efectivo', 'cantidad' => $ventas->where('metodo_pago','efectivo')->count(), 'total' => $totalEfectivo],
            ['metodo_pago' => 'yape',     'cantidad' => $ventas->where('metodo_pago','yape')->count(),     'total' => $totalYape],
            ['metodo_pago' => 'plin',     'cantidad' => $ventas->where('metodo_pago','plin')->count(),     'total' => $totalPlin],
            ['metodo_pago' => 'tarjeta',  'cantidad' => $ventas->where('metodo_pago','tarjeta')->count(),  'total' => $totalTarjeta],
        ])->filter(fn($m) => $m['total'] > 0)->values();

        return Inertia::render('Farmacia/Reportes', [
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
            'metodos_pago'   => $metodosPago,
            'desde'          => $desde,
            'hasta'          => $hasta,
        ]);
    }

    public function ventas(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $desde = $request->desde ?? now()->toDateString();
        $hasta = $request->hasta ?? now()->toDateString();

        $ventas = \App\Models\Venta::where('empresa_id', $empresaId)
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->orderBy('fecha_emision', 'desc')
            ->paginate(20);

        return \Inertia\Inertia::render('Farmacia/Ventas', [
            'ventas' => $ventas,
            'desde'  => $desde,
            'hasta'  => $hasta,
        ]);
    }

    public function show($id)
    {
        $venta   = \App\Models\Venta::with('detalle')->findOrFail($id);
        $empresa = auth()->user()->empresa;
        return \Inertia\Inertia::render('Farmacia/VentaDetalle', [
            'venta'   => $venta,
            'empresa' => $empresa,
        ]);
    }
}
