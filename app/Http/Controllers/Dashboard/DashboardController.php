<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\EmpresaHelper;
use App\Http\Controllers\Controller;
use App\Models\CajaRestaurante;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Sucursal;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $empresaId = EmpresaHelper::id();
        $hoy = now()->toDateString();

        // Ventas del dia = lo cobrado en caja_restaurante (mesas/POS), no la
        // tabla generica "ventas" (esa es del flujo de facturacion retail y
        // el restaurante no la usa para sus cobros de mesa).
        $ventasHoy = CajaRestaurante::with('mesa:id,sucursal_id')
            ->where('empresa_id', $empresaId)
            ->whereDate('created_at', $hoy)
            ->get();

        $totalVentasHoy    = $ventasHoy->sum('total');
        $cantidadVentasHoy = $ventasHoy->count();
        $ticketPromedio    = $cantidadVentasHoy > 0 ? $totalVentasHoy / $cantidadVentasHoy : 0;

        $totalMesas    = Mesa::where('empresa_id', $empresaId)->where('activo', true)->count();
        $mesasOcupadas = Mesa::where('empresa_id', $empresaId)->where('activo', true)->where('estado', 'ocupada')->count();

        $pedidosPendientesCocina = Pedido::where('empresa_id', $empresaId)->where('estado', 'enviado')->count();

        $sucursales = Sucursal::where('empresa_id', $empresaId)->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
        $ventasPorSucursal = $sucursales->map(function (Sucursal $s) use ($ventasHoy) {
            $ventasSucursal = $ventasHoy->filter(fn($v) => $v->mesa && $v->mesa->sucursal_id === $s->id);
            return [
                'id'     => $s->id,
                'nombre' => $s->nombre,
                'total'  => round($ventasSucursal->sum('total'), 2),
            ];
        });

        $ultimosPedidos = Pedido::with(['mesa:id,numero', 'detalles'])
            ->where('empresa_id', $empresaId)
            ->whereDate('created_at', $hoy)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn(Pedido $p) => [
                'id'     => $p->id,
                'mesa'   => $p->mesa->numero ?? '—',
                'items'  => $p->detalles->count(),
                'total'  => round($p->total, 2),
                'estado' => $p->estado,
            ]);

        $topPlatos = PedidoDetalle::selectRaw('nombre_producto, SUM(cantidad) as total_cantidad')
            ->whereHas('pedido', fn($q) => $q->where('empresa_id', $empresaId)->whereDate('created_at', $hoy))
            ->groupBy('nombre_producto')
            ->orderByDesc('total_cantidad')
            ->limit(5)
            ->get();

        $porMetodoPago = $ventasHoy->groupBy('metodo_pago')->map(fn($g) => [
            'cantidad' => $g->count(),
            'total'    => round($g->sum('total'), 2),
        ]);

        return Inertia::render('Dashboard/Index', [
            'kpis' => [
                'ventas_hoy'                => round($totalVentasHoy, 2),
                'cantidad_ventas_hoy'       => $cantidadVentasHoy,
                'ticket_promedio'           => round($ticketPromedio, 2),
                'mesas_ocupadas'            => $mesasOcupadas,
                'total_mesas'               => $totalMesas,
                'pedidos_pendientes_cocina' => $pedidosPendientesCocina,
            ],
            'ventas_por_sucursal' => $ventasPorSucursal,
            'ultimos_pedidos'     => $ultimosPedidos,
            'top_platos'          => $topPlatos,
            'por_metodo_pago'     => $porMetodoPago,
        ]);
    }
}
