<?php

namespace App\Http\Controllers;

use App\Helpers\SucursalHelper;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CocinaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Cocina/Index', [
            'pedidos'         => $this->pedidosPendientes(),
            'sucursalNombre'  => $this->sucursalNombre(),
        ]);
    }

    /**
     * Endpoint de solo datos para el auto-refresh del KDS (fetch periodico
     * desde el cliente, sin pasar por una recarga de pagina de Inertia).
     */
    public function polling()
    {
        return response()->json([
            'pedidos' => $this->pedidosPendientes(),
        ]);
    }

    private function pedidosPendientes()
    {
        $empresa_id = auth()->user()->empresa_id;

        return SucursalHelper::aplicarFiltro(
            Pedido::with(['detalles', 'mesa.sucursal', 'sucursal'])
                ->where('empresa_id', $empresa_id)
                ->whereIn('estado', ['enviado'])
        )
            ->orderBy('created_at', 'asc')
            ->get();
    }

    private function sucursalNombre(): ?string
    {
        $sucursal = auth()->user()->sucursal;
        if ($sucursal) {
            return $sucursal->nombre;
        }

        return auth()->user()->empresa->nombre_comercial ?? null;
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
