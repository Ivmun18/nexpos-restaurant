<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\CajaRestaurante;
use App\Models\User;
use App\Models\MenuProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ReporteRestauranteController extends Controller
{
    /**
     * Vista principal de reportes
     */
    public function index(Request $request): Response
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->toDateString());
$fechaFin = $request->input('fecha_fin', Carbon::today()->toDateString());
$tipoReporte = $request->input('tipo', 'mes');

        // Calcular fechas según tipo de reporte
        if ($tipoReporte === 'dia') {
            $fechaInicio = Carbon::today()->toDateString();
            $fechaFin = Carbon::today()->toDateString();
        } elseif ($tipoReporte === 'semana') {
            $fechaInicio = Carbon::now()->startOfWeek()->toDateString();
            $fechaFin = Carbon::now()->endOfWeek()->toDateString();
        } elseif ($tipoReporte === 'mes') {
            $fechaInicio = Carbon::now()->startOfMonth()->toDateString();
            $fechaFin = Carbon::now()->endOfMonth()->toDateString();
        }

        $data = [
            'resumen' => $this->getResumenVentas($fechaInicio, $fechaFin),
            'ventasPorDia' => $this->getVentasPorDia($fechaInicio, $fechaFin),
            'productosTop' => $this->getProductosTop($fechaInicio, $fechaFin),
            'mozosTop' => $this->getMozosTop($fechaInicio, $fechaFin),
            'metodosPago' => $this->getMetodosPago($fechaInicio, $fechaFin),
            'ventasPorHora' => $this->getVentasPorHora($fechaInicio, $fechaFin),
            'comparativa' => $this->getComparativaPeriodo($fechaInicio, $fechaFin, $tipoReporte),
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'tipo' => $tipoReporte,
            ],
        ];

        return Inertia::render('Reportes/Index', $data);
    }

    /**
     * Resumen general de ventas
     */
    private function getResumenVentas(string $fechaInicio, string $fechaFin): array
    {
        $pedidos = Pedido::whereBetween('created_at', [
            Carbon::parse($fechaInicio)->startOfDay(),
            Carbon::parse($fechaFin)->endOfDay()
        ])
        ->where('estado', 'cerrado')
        ->select(
            DB::raw('COUNT(*) as total_pedidos'),
            DB::raw('SUM(total) as total_ingresos'),
            DB::raw('AVG(total) as ticket_promedio'),
            DB::raw('COUNT(DISTINCT mesa_id) as mesas_atendidas')
        )
        ->first();

        $cajas = CajaRestaurante::whereBetween('created_at', [
            Carbon::parse($fechaInicio)->startOfDay(),
            Carbon::parse($fechaFin)->endOfDay()
        ])
        ->select(
            DB::raw('COUNT(*) as turnos_abiertos'),
            DB::raw('SUM(total) as total_cajas')
        )
        ->first();

        return [
            'total_ingresos' => $pedidos->total_ingresos ?? 0,
            'total_pedidos' => $pedidos->total_pedidos ?? 0,
            'ticket_promedio' => $pedidos->ticket_promedio ?? 0,
            'mesas_atendidas' => $pedidos->mesas_atendidas ?? 0,
            'turnos_abiertos' => $cajas->turnos_abiertos ?? 0,
        ];
    }

    /**
     * Ventas agrupadas por día
     */
    private function getVentasPorDia(string $fechaInicio, string $fechaFin): array
    {
        $ventas = Pedido::whereBetween('created_at', [
            Carbon::parse($fechaInicio)->startOfDay(),
            Carbon::parse($fechaFin)->endOfDay()
        ])
        ->where('estado', 'cerrado')
        ->select(
            DB::raw('DATE(created_at) as fecha'),
            DB::raw('COUNT(*) as total_pedidos'),
            DB::raw('SUM(total) as total_ventas')
        )
        ->groupBy('fecha')
        ->orderBy('fecha', 'asc')
        ->get()
        ->map(function ($item) {
            return [
                'fecha' => Carbon::parse($item->fecha)->format('d/m/Y'),
                'fecha_corta' => Carbon::parse($item->fecha)->format('d M'),
                'total_pedidos' => $item->total_pedidos,
                'total_ventas' => floatval($item->total_ventas),
            ];
        });

        return $ventas->toArray();
    }

    /**
     * Productos más vendidos
     */
    private function getProductosTop(string $fechaInicio, string $fechaFin, int $limite = 10): array
    {
        $productos = PedidoDetalle::join('pedidos', 'pedido_detalles.pedido_id', '=', 'pedidos.id')
            ->join('menu_productos', 'pedido_detalles.menu_producto_id', '=', 'menu_productos.id')
            ->whereBetween('pedidos.created_at', [
                Carbon::parse($fechaInicio)->startOfDay(),
                Carbon::parse($fechaFin)->endOfDay()
            ])
            ->where('pedidos.estado', 'pagado')
            ->select(
                'menu_productos.nombre as producto',
                DB::raw('SUM(pedido_detalles.cantidad) as cantidad_vendida'),
                DB::raw('SUM(pedido_detalles.subtotal) as total_generado')
            )
            ->groupBy('menu_productos.id', 'menu_productos.nombre')
            ->orderBy('cantidad_vendida', 'desc')
            ->limit($limite)
            ->get()
            ->map(function ($item) {
                return [
                    'producto' => $item->producto,
                    'cantidad' => intval($item->cantidad_vendida),
                    'total' => floatval($item->total_generado),
                ];
            });

        return $productos->toArray();
    }

    /**
     * Mozos con mejores ventas
     */
    private function getMozosTop(string $fechaInicio, string $fechaFin, int $limite = 10): array
    {
        $mozos = Pedido::join('users', 'pedidos.user_id', '=', 'users.id')
            ->whereBetween('pedidos.created_at', [
                Carbon::parse($fechaInicio)->startOfDay(),
                Carbon::parse($fechaFin)->endOfDay()
            ])
            ->where('pedidos.estado', 'pagado')
            ->where('users.rol', 'mozo')
            ->select(
                'users.name as mozo',
                DB::raw('COUNT(pedidos.id) as total_pedidos'),
                DB::raw('SUM(pedidos.total) as total_ventas'),
                DB::raw('AVG(pedidos.total) as ticket_promedio')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_ventas', 'desc')
            ->limit($limite)
            ->get()
            ->map(function ($item) {
                return [
                    'mozo' => $item->mozo,
                    'pedidos' => intval($item->total_pedidos),
                    'ventas' => floatval($item->total_ventas),
                    'promedio' => floatval($item->ticket_promedio),
                ];
            });

        return $mozos->toArray();
    }

    /**
     * Distribución de métodos de pago
     */
    private function getMetodosPago(string $fechaInicio, string $fechaFin): array
    {
        $metodos = CajaRestaurante::whereBetween('created_at', [
            Carbon::parse($fechaInicio)->startOfDay(),
            Carbon::parse($fechaFin)->endOfDay()
        ])
        ->select('metodo_pago', DB::raw('SUM(total) as total'))
        ->groupBy('metodo_pago')
        ->get();

        $colores = [
            'efectivo' => '#10B981',
            'yape' => '#8B5CF6',
            'plin' => '#EC4899',
            'tarjeta' => '#3B82F6',
        ];

        return $metodos->map(function($metodo) use ($colores) {
            return [
                'metodo' => ucfirst($metodo->metodo_pago),
                'total' => floatval($metodo->total),
                'color' => $colores[strtolower($metodo->metodo_pago)] ?? '#94A3B8',
            ];
        })->toArray();
    }

    /**
     * Ventas por hora del día
     */
    private function getVentasPorHora(string $fechaInicio, string $fechaFin): array
    {
        $ventas = Pedido::whereBetween('created_at', [
            Carbon::parse($fechaInicio)->startOfDay(),
            Carbon::parse($fechaFin)->endOfDay()
        ])
        ->where('estado', 'cerrado')
        ->select(
            DB::raw('HOUR(created_at) as hora'),
            DB::raw('COUNT(*) as total_pedidos'),
            DB::raw('SUM(total) as total_ventas')
        )
        ->groupBy('hora')
        ->orderBy('hora', 'asc')
        ->get()
        ->map(function ($item) {
            return [
                'hora' => sprintf('%02d:00', $item->hora),
                'pedidos' => intval($item->total_pedidos),
                'ventas' => floatval($item->total_ventas),
            ];
        });

        return $ventas->toArray();
    }

    /**
     * Comparativa con período anterior
     */
    private function getComparativaPeriodo(string $fechaInicio, string $fechaFin, string $tipo): array
    {
        $inicio = Carbon::parse($fechaInicio);
        $fin = Carbon::parse($fechaFin);
        $dias = $inicio->diffInDays($fin) + 1;

        // Calcular período anterior
        $periodoAnteriorFin = $inicio->copy()->subDay();
        $periodoAnteriorInicio = $periodoAnteriorFin->copy()->subDays($dias - 1);

        // Ventas período actual
        $ventasActual = Pedido::whereBetween('created_at', [
            $inicio->startOfDay(),
            $fin->endOfDay()
        ])
        ->where('estado', 'cerrado')
        ->sum('total');

        // Ventas período anterior
        $ventasAnterior = Pedido::whereBetween('created_at', [
            $periodoAnteriorInicio->startOfDay(),
            $periodoAnteriorFin->endOfDay()
        ])
        ->where('estado', 'cerrado')
        ->sum('total');

        $diferencia = $ventasActual - $ventasAnterior;
        $porcentaje = $ventasAnterior > 0 ? (($diferencia / $ventasAnterior) * 100) : 0;

        return [
            'actual' => floatval($ventasActual),
            'anterior' => floatval($ventasAnterior),
            'diferencia' => floatval($diferencia),
            'porcentaje' => round($porcentaje, 2),
            'crecimiento' => $diferencia >= 0,
        ];
    }

    /**
     * Exportar reporte a Excel (placeholder)
     */
    public function exportarExcel(Request $request)
    {
        // TODO: Implementar exportación con Laravel Excel
        return response()->json(['message' => 'Función en desarrollo']);
    }

    /**
     * Exportar reporte a PDF (placeholder)
     */
    public function exportarPdf(Request $request)
    {
        // TODO: Implementar exportación con DomPDF o similar
        return response()->json(['message' => 'Función en desarrollo']);
    }
}
