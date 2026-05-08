<?php

namespace App\Http\Controllers\Ferreteria;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Cotizacion;
use App\Models\OrdenTrabajo;
use App\Models\Garantia;
use App\Models\CajaMinimarket as Caja;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportesFerretoriaController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $desde = $request->desde ?? now()->startOfMonth()->toDateString();
        $hasta = $request->hasta ?? now()->toDateString();

        // Productos con stock bajo
        $stockBajo = Producto::where('empresa_id', $empresa_id)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderBy('stock_actual')
            ->get();

        // Valor inventario
        $valorInventario = Producto::where('empresa_id', $empresa_id)
            ->selectRaw('SUM(stock_actual * precio_compra) as valor, SUM(stock_actual * precio_venta) as valor_venta, COUNT(*) as total')
            ->first();

        // Cotizaciones del período
        $cotizaciones = Cotizacion::where('empresa_id', $empresa_id)
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->get();

        // Órdenes del período
        $ordenes = OrdenTrabajo::where('empresa_id', $empresa_id)
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->get();

        // Garantías
        $garantias = Garantia::where('empresa_id', $empresa_id)->get();

        // Cajas cerradas del período
        $cajas = Caja::where('empresa_id', $empresa_id)
            ->where('estado', 'cerrada')
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->get();

        return Inertia::render('Ferreteria/Reportes', [
            'desde'           => $desde,
            'hasta'           => $hasta,
            'stock_bajo'      => $stockBajo,
            'valor_inventario'=> $valorInventario,
            'resumen_cotizaciones' => [
                'total'     => $cotizaciones->count(),
                'aprobadas' => $cotizaciones->where('estado', 'aprobada')->count(),
                'monto'     => round($cotizaciones->where('estado', 'aprobada')->sum('total'), 2),
            ],
            'resumen_ordenes' => [
                'total'      => $ordenes->count(),
                'completadas'=> $ordenes->whereIn('estado', ['completada', 'entregada'])->count(),
                'monto'      => round($ordenes->sum('total'), 2),
            ],
            'resumen_garantias' => [
                'activas'   => $garantias->where('estado', 'activa')->count(),
                'vencidas'  => $garantias->where('estado', 'vencida')->count(),
                'reclamadas'=> $garantias->where('estado', 'reclamada')->count(),
            ],
            'resumen_caja' => [
                'total_ventas'  => round($cajas->sum('total_ventas'), 2),
                'total_efectivo'=> round($cajas->sum('total_efectivo'), 2),
                'total_yape'    => round($cajas->sum('total_yape'), 2),
                'total_tarjeta' => round($cajas->sum('total_tarjeta'), 2),
                'aperturas'     => $cajas->count(),
            ],
        ]);
    }
}
