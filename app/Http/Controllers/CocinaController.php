<?php

namespace App\Http\Controllers;

use App\Helpers\SucursalHelper;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CocinaController extends Controller
{
    public function index(Request $request): Response
    {
        $sucursalId = $request->integer('sucursal_id') ?: null;

        return Inertia::render('Cocina/Index', [
            'pedidos'         => $this->pedidosPendientes($sucursalId),
            'sucursalNombre'  => $this->sucursalNombre($sucursalId),
            'sucursales'      => $this->sucursalesDisponibles(),
            'sucursalActual'  => $sucursalId,
            'esAdmin'         => SucursalHelper::verTodas(),
        ]);
    }

    /**
     * Endpoint de solo datos para el auto-refresh del KDS (fetch periodico
     * desde el cliente, sin pasar por una recarga de pagina de Inertia).
     */
    public function polling(Request $request)
    {
        $sucursalId = $request->integer('sucursal_id') ?: null;

        return response()->json([
            'pedidos' => $this->pedidosPendientes($sucursalId),
        ]);
    }

    private function pedidosPendientes(?int $sucursalId = null)
    {
        $empresa_id = auth()->user()->empresa_id;

        $query = Pedido::with(['detalles', 'mesa.sucursal', 'sucursal'])
            ->where('empresa_id', $empresa_id)
            ->whereIn('estado', ['enviado']);

        // Solo un admin puede elegir explícitamente qué sucursal ver;
        // cualquier otro rol siempre queda acotado a la suya vía el helper.
        if ($sucursalId && SucursalHelper::verTodas()) {
            $query->where('sucursal_id', $sucursalId);
        } else {
            SucursalHelper::aplicarFiltro($query);
        }

        return $query->orderBy('created_at', 'asc')->get();
    }

    private function sucursalNombre(?int $sucursalId = null): ?string
    {
        if ($sucursalId) {
            $sucursal = Sucursal::find($sucursalId);
            if ($sucursal) {
                return $sucursal->nombre;
            }
        }

        $sucursal = auth()->user()->sucursal;
        if ($sucursal) {
            return $sucursal->nombre;
        }

        return auth()->user()->empresa->nombre_comercial ?? null;
    }

    private function sucursalesDisponibles()
    {
        if (!SucursalHelper::verTodas()) {
            return [];
        }

        return Sucursal::where('empresa_id', auth()->user()->empresa_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    public function marcarListo(Pedido $pedido)
    {
        $pedido->update(['estado' => 'listo']);

        return redirect()->back()->with('success', "Pedido listo.");
    }

    public function marcarDetalleListo(PedidoDetalle $pedidoDetalle)
    {
        $pedidoDetalle->update(['estado' => 'listo']);

        return response()->json(['estado' => 'listo']);
    }
}
