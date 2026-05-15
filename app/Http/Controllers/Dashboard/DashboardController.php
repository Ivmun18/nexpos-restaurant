<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\EmpresaHelper;
use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\Venta;
use App\Models\CuentaPorPagar;
use App\Models\Producto;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $empresaId = EmpresaHelper::id();
        $hoy = now()->toDateString();
        $hace30Dias = now()->subDays(30)->toDateString();

        // KPIs Principales
        $kpis = [
            'ventas_hoy' => $this->ventasHoy($empresaId),
            'compras_hoy' => $this->comprasHoy($empresaId),
            'cuentas_vencidas' => $this->cuentasVencidas($empresaId),
            'stock_bajo' => $this->stockBajo($empresaId),
        ];

        // Datos para gráficos - Últimos 30 días
        $ventasPorDia = $this->ventasPorDia($empresaId, $hace30Dias);
        $comprasPorDia = $this->comprasPorDia($empresaId, $hace30Dias);
        
        // Cuentas por pagar próximas a vencer
        $cuentasProximas = CuentaPorPagar::where('empresa_id', $empresaId)
            ->porVencer()
            ->with('proveedor')
            ->limit(5)
            ->get();

        // Top 5 proveedores por monto
        $topProveedores = $this->topProveedores($empresaId);

        // Productos con stock bajo
        $productosStockBajo = Producto::where('empresa_id', $empresaId)
            ->where('controla_stock', true)
            ->whereRaw('stock_actual <= stock_minimo')
            ->orderBy('stock_actual', 'asc')
            ->limit(10)
            ->get();

        // Actividad reciente (últimas 10 acciones)
        $actividadReciente = AuditLog::where('empresa_id', $empresaId)
            ->with('usuario')
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard/Index', [
            'kpis' => $kpis,
            'ventasPorDia' => $ventasPorDia,
            'comprasPorDia' => $comprasPorDia,
            'cuentasProximas' => $cuentasProximas,
            'topProveedores' => $topProveedores,
            'productosStockBajo' => $productosStockBajo,
            'actividadReciente' => $actividadReciente,
        ]);
    }

    private function ventasHoy($empresaId)
    {
        return Venta::where('empresa_id', $empresaId)
            ->whereDate('created_at', now())
            ->sum('total');
    }

    private function comprasHoy($empresaId)
    {
        return Compra::where('empresa_id', $empresaId)
            ->whereDate('created_at', now())
            ->sum('total');
    }

    private function cuentasVencidas($empresaId)
    {
        return CuentaPorPagar::where('empresa_id', $empresaId)
            ->vencido()
            ->count();
    }

    private function stockBajo($empresaId)
    {
        return Producto::where('empresa_id', $empresaId)
            ->where('controla_stock', true)
            ->whereRaw('stock_actual <= stock_minimo')
            ->count();
    }

    private function ventasPorDia($empresaId, $desde)
    {
        return Venta::where('empresa_id', $empresaId)
            ->where('created_at', '>=', $desde)
            ->selectRaw('DATE(created_at) as fecha, SUM(total) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->pluck('total', 'fecha');
    }

    private function comprasPorDia($empresaId, $desde)
    {
        return Compra::where('empresa_id', $empresaId)
            ->where('created_at', '>=', $desde)
            ->selectRaw('DATE(created_at) as fecha, SUM(total) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->pluck('total', 'fecha');
    }

    private function topProveedores($empresaId)
    {
        return Compra::where('empresa_id', $empresaId)
            ->selectRaw('proveedor_id, SUM(total) as total_compras, COUNT(*) as cantidad')
            ->with('proveedor')
            ->groupBy('proveedor_id')
            ->orderByDesc('total_compras')
            ->limit(5)
            ->get();
    }
}
