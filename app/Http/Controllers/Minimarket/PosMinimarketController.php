<?php

namespace App\Http\Controllers\Minimarket;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosMinimarketController extends Controller
{
    public function index()
    {
        $productos = Producto::where('empresa_id', auth()->user()->empresa_id)
            ->where('activo', true)
            ->orderBy('descripcion')
            ->get(['id', 'descripcion', 'descripcion_corta', 'codigo_barras', 'precio_venta', 'stock_actual', 'categoria_id']);

        return Inertia::render('Minimarket/Pos', [
            'productos' => $productos,
        ]);
    }

    public function store(Request $request)
{
    $request->validate([
        'items'        => 'required|array|min:1',
        'metodo_pago'  => 'required|string',
        'total'        => 'required|numeric',
        'monto_pagado' => 'nullable|numeric',
    ]);

    $correlativo = (\App\Models\Venta::where('empresa_id', auth()->user()->empresa_id)->max('correlativo') ?? 0) + 1;

    $venta = \App\Models\Venta::create([
        'empresa_id'          => auth()->user()->empresa_id,
        'usuario_id'          => auth()->id(),
        'tipo_comprobante'    => '03',
        'serie'               => 'B001',
        'correlativo'         => $correlativo,
        'numero_completo'     => 'B001-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT),
        'fecha_emision'       => now()->toDateString(),
        'hora_emision'        => now()->toTimeString(),
        'moneda'              => 'PEN',
        'total_gravado'       => $request->total,
        'total_exonerado'     => 0,
        'total_inafecto'      => 0,
        'total_descuento'     => 0,
    ]);

   foreach ($request->items as $index => $item) {
    \App\Models\VentaDetalle::create([
        'venta_id'           => $venta->id,
        'producto_id'        => $item['id'],
        'linea'              => $index + 1,
        'codigo_producto'    => $item['codigo_barras'] ?? $item['codigo'] ?? 'S/C',
        'descripcion'        => $item['descripcion'],
        'unidad_medida'      => 'NIU',
        'cantidad'           => $item['cantidad'],
        'precio_unitario'    => $item['precio_venta'],
        'valor_unitario'     => $item['precio_venta'],
        'descuento_monto'    => 0,
        'tipo_afectacion_igv'=> '10',
        'total_valor_venta'  => $item['cantidad'] * $item['precio_venta'],
        'total_igv'          => 0,
        'total'              => $item['cantidad'] * $item['precio_venta'],
    ]);

    \App\Models\Producto::where('id', $item['id'])->decrement('stock_actual', $item['cantidad']);
}

    return redirect()->route('minimarket.ventas.show', $venta->id)
    ->with('success', 'Venta registrada correctamente')
    ->with('imprimir', true); 
}
}