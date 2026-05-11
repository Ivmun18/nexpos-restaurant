<?php

namespace App\Http\Controllers\Minimarket;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VentasMinimarketController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $desde = $request->desde ?? now()->startOfMonth()->toDateString();
        $hasta = $request->hasta ?? now()->toDateString();

        $ventas = Venta::with('detalle')
            ->where('empresa_id', $empresaId)
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->orderBy('fecha_emision', 'desc')
            ->paginate(20);

        return Inertia::render('Minimarket/Ventas', [
            'ventas' => $ventas,
            'desde'  => $desde,
            'hasta'  => $hasta,
        ]);
    }

   public function show($id)
{
    $venta = Venta::with('detalle.producto')
        ->where('empresa_id', auth()->user()->empresa_id)
        ->findOrFail($id);

    return Inertia::render('Minimarket/VentaDetalle', [
        'venta'    => $venta,
        'empresa'  => auth()->user()->empresa,
        'imprimir' => session('imprimir', false),
    ]);
}
}