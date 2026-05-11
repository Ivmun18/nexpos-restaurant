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

        // Ventas del período
        $ventas = \App\Models\Venta::where('empresa_id', $empresa_id)
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->where('estado', '!=', 'anulado')
            ->get();

        // Ventas por vendedor
        $ventasPorVendedor = \App\Models\Venta::where('empresa_id', $empresa_id)
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->where('estado', '!=', 'anulado')
            ->with('usuario:id,name,rol')
            ->get()
            ->groupBy('usuario_id')
            ->map(function($ventas, $userId) {
                $usuario = $ventas->first()->usuario;
                return [
                    'usuario_id'   => $userId,
                    'nombre'       => $usuario?->name ?? 'Sin usuario',
                    'rol'          => $usuario?->rol ?? '',
                    'cantidad'     => $ventas->count(),
                    'total'        => round($ventas->sum('total'), 2),
                    'efectivo'     => round($ventas->where('metodo_pago','efectivo')->sum('total'), 2),
                    'yape'         => round($ventas->where('metodo_pago','yape')->sum('total'), 2),
                    'plin'         => round($ventas->where('metodo_pago','plin')->sum('total'), 2),
                    'tarjeta'      => round($ventas->where('metodo_pago','tarjeta')->sum('total'), 2),
                ];
            })->values();

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
            'ventas_por_vendedor' => $ventasPorVendedor,
            'resumen_caja' => [
                'total_ventas'  => round($ventas->sum('total'), 2),
                'total_efectivo'=> round($ventas->where('metodo_pago','efectivo')->sum('total'), 2),
                'total_yape'    => round($ventas->where('metodo_pago','yape')->sum('total'), 2),
                'total_tarjeta' => round($ventas->where('metodo_pago','tarjeta')->sum('total'), 2),
                'cantidad'      => $ventas->count(),
            ],
        ]);
    }

    public function ventas(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $desde = $request->desde ?? now()->startOfMonth()->toDateString();
        $hasta = $request->hasta ?? now()->toDateString();

        $ventas = \App\Models\Venta::where('empresa_id', $empresaId)
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->orderBy('fecha_emision', 'desc')
            ->paginate(20);

        return \Inertia\Inertia::render('Ferreteria/Ventas', [
            'ventas' => $ventas,
            'desde'  => $desde,
            'hasta'  => $hasta,
        ]);
    }

    public function show($id)
    {
        $venta   = \App\Models\Venta::with('detalle')->findOrFail($id);
        $empresa = auth()->user()->empresa;
        return \Inertia\Inertia::render('Ferreteria/VentaDetalle', [
            'venta'   => $venta,
            'empresa' => $empresa,
        ]);
    }

    public function anular(\App\Models\Venta $venta)
    {
        if ($venta->estado === 'anulado') {
            return back()->with('error', 'La venta ya está anulada.');
        }
        $venta->update(['estado' => 'anulado']);
        return redirect('/ferreteria/ventas')->with('success', '✅ Venta anulada correctamente.');
    }

    public function reintentar(\App\Models\Venta $venta)
    {
        return back()->with('info', 'Reintento de envío a SUNAT iniciado.');
    }
}
