<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReporteMozoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId  = $request->user()->empresa_id;
        $fechaDesde = $request->get('fecha_desde', now()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->toDateString());
        $sucursalId = $request->get('sucursal_id', '');

        $desde = $fechaDesde . ' 00:00:00';
        $hasta = $fechaHasta . ' 23:59:59';

        $ventasQuery = PedidoDetalle::query()
            ->join('pedidos', 'pedidos.id', '=', 'pedido_detalles.pedido_id')
            ->where('pedidos.empresa_id', $empresaId)
            ->whereBetween('pedidos.created_at', [$desde, $hasta])
            ->where('pedido_detalles.pagado', true)
            ->where('pedido_detalles.anulado', false)
            ->when($sucursalId, fn ($q) => $q->where('pedidos.sucursal_id', $sucursalId));

        $porMozo = (clone $ventasQuery)
            ->select(
                'pedidos.user_id',
                DB::raw('SUM(pedido_detalles.subtotal) as total_vendido'),
                DB::raw('COUNT(DISTINCT pedidos.id) as num_pedidos'),
                DB::raw('COUNT(DISTINCT pedidos.mesa_id) as num_mesas')
            )
            ->groupBy('pedidos.user_id')
            ->get()
            ->keyBy('user_id');

        $mozos = User::query()
            ->whereIn('id', $porMozo->keys())
            ->where('empresa_id', $empresaId)
            ->with('sucursal:id,nombre')
            ->get(['id', 'name', 'sucursal_id']);

        $reporte = $mozos->map(function ($mozo) use ($porMozo) {
            $fila = $porMozo->get($mozo->id);
            $totalVendido = (float) $fila->total_vendido;
            $numPedidos   = (int) $fila->num_pedidos;

            return [
                'mozo_id'         => $mozo->id,
                'nombre'          => $mozo->name,
                'local'           => $mozo->sucursal->nombre ?? '—',
                'num_pedidos'     => $numPedidos,
                'num_mesas'       => (int) $fila->num_mesas,
                'total_vendido'   => round($totalVendido, 2),
                'ticket_promedio' => $numPedidos > 0 ? round($totalVendido / $numPedidos, 2) : 0,
            ];
        })->sortByDesc('total_vendido')->values();

        $sucursales = Sucursal::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return Inertia::render('Reportes/Mozos', [
            'reporte'    => $reporte,
            'resumen'    => [
                'total_vendido' => round($reporte->sum('total_vendido'), 2),
                'num_pedidos'   => $reporte->sum('num_pedidos'),
                'mozo_top'      => $reporte->first()['nombre'] ?? null,
            ],
            'sucursales' => $sucursales,
            'filtros'    => [
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'sucursal_id' => $sucursalId,
            ],
        ]);
    }

    public function detalle(Request $request, User $mozo)
    {
        if ($mozo->empresa_id !== $request->user()->empresa_id) {
            abort(403);
        }

        $fechaDesde = $request->get('fecha_desde', now()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->toDateString());
        $sucursalId = $request->get('sucursal_id', '');

        $desde = $fechaDesde . ' 00:00:00';
        $hasta = $fechaHasta . ' 23:59:59';

        $pedidos = Pedido::query()
            ->where('empresa_id', $mozo->empresa_id)
            ->where('user_id', $mozo->id)
            ->whereBetween('created_at', [$desde, $hasta])
            ->when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))
            ->with('mesa:id,numero')
            ->withSum(['detalles as total_cobrado' => function ($q) {
                $q->where('pagado', true)->where('anulado', false);
            }], 'subtotal')
            ->orderByDesc('created_at')
            ->get(['id', 'mesa_id', 'estado', 'created_at']);

        return response()->json([
            'mozo'    => ['id' => $mozo->id, 'nombre' => $mozo->name],
            'pedidos' => $pedidos->map(fn ($p) => [
                'id'      => $p->id,
                'fecha'   => $p->created_at,
                'mesa'    => $p->mesa->numero ?? '—',
                'total'   => round((float) ($p->total_cobrado ?? 0), 2),
                'estado'  => $p->estado,
            ]),
        ]);
    }
}
