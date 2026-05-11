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
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->get();

        $totalPeriodo   = $ventas->sum('total_gravado');
        $totalEfectivo  = $ventas->where('metodo_pago', 'efectivo')->sum('total_gravado');
        $totalYape      = $ventas->where('metodo_pago', 'yape')->sum('total_gravado');
        $totalPlin      = $ventas->where('metodo_pago', 'plin')->sum('total_gravado');
        $totalTarjeta   = $ventas->where('metodo_pago', 'tarjeta')->sum('total_gravado');

        // Ventas por día
        $ventasPorDia = Venta::where('empresa_id', $empresaId)
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->selectRaw('DATE(created_at) as fecha, COUNT(*) as cantidad, SUM(total_gravado) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Top productos
        $topProductos = VentaDetalle::selectRaw('descripcion, SUM(cantidad) as total_cantidad, SUM(total) as total_monto')
            ->whereHas('venta', fn($q) => $q->where('empresa_id', $empresaId)
                ->whereDate('created_at', '>=', $desde)
                ->whereDate('created_at', '<=', $hasta))
            ->groupBy('descripcion')
            ->orderByDesc('total_monto')
            ->limit(10)
            ->get();

       // Métodos de pago desde cajas_minimarket
$metodosPago = collect([
    ['metodo_pago' => 'efectivo', 'cantidad' => $ventas->count(), 'total' => $totalEfectivo],
    ['metodo_pago' => 'yape',     'cantidad' => 0, 'total' => $totalYape],
    ['metodo_pago' => 'plin',     'cantidad' => 0, 'total' => $totalPlin],
    ['metodo_pago' => 'tarjeta',  'cantidad' => 0, 'total' => $totalTarjeta],
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

    public function ventas()
    {
        $empresaId = auth()->user()->empresa_id;
        $ventas = \App\Models\Venta::where('empresa_id', $empresaId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return \Inertia\Inertia::render('Farmacia/Ventas', [
            'ventas' => $ventas,
        ]);
    }

    public function show($id)
    {
        $venta = \App\Models\Venta::with('detalle')->findOrFail($id);
        return \Inertia\Inertia::render('Farmacia/VentaDetalle', [
            'venta' => $venta,
        ]);
    }
}
