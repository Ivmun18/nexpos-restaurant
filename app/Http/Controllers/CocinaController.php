<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoDetalle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CocinaController extends Controller
{
    public function index(): Response
    {
        $pedidos = Pedido::with(['detalles', 'mesa'])
            ->whereIn('estado', ['enviado'])
            ->orderBy('created_at', 'asc')
            ->get();

        return Inertia::render('Cocina/Index', [
            'pedidos' => $pedidos,
        ]);
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