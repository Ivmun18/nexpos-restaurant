<?php

namespace App\Http\Controllers\Minimarket;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VentasMinimarketController extends Controller
{
    public function index()
    {
        $ventas = Venta::with('detalle')
            ->where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Minimarket/Ventas', [
            'ventas' => $ventas,
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