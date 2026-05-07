<?php

namespace App\Http\Controllers\Minimarket;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\CategoriaMinimarket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductosMinimarketController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;

        $productos = Producto::where('empresa_id', $empresa_id)
            ->with('categoria')
            ->orderBy('descripcion')
            ->get();

        $categorias = CategoriaMinimarket::where('empresa_id', $empresa_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Minimarket/Productos', [
            'productos'  => $productos,
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;

        $request->validate([
            'descripcion'  => 'required|string|max:255',
            'codigo'       => 'nullable|string|max:50',
            'precio_venta' => 'required|numeric|min:0',
            'precio_compra'=> 'nullable|numeric|min:0',
            'stock_actual' => 'nullable|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
            'codigo_barras'=> 'nullable|string|max:100',
            'categoria_id' => 'nullable|exists:categorias_minimarket,id',
        ]);

        Producto::create([
            'empresa_id'    => $empresa_id,
            'categoria_id'  => $request->categoria_id,
            'descripcion'   => $request->descripcion,
            'codigo'        => $request->codigo ?? 'P' . str_pad(Producto::where('empresa_id', $empresa_id)->count() + 1, 3, '0', STR_PAD_LEFT),
            'precio_venta'  => $request->precio_venta,
            'precio_compra' => $request->precio_compra ?? 0,
            'stock_actual'  => $request->stock_actual ?? 0,
            'stock_minimo'  => $request->stock_minimo ?? 0,
            'codigo_barras' => $request->codigo_barras,
            'activo'        => true,
            'controla_stock'=> true,
        ]);

        return redirect()->route('minimarket.productos')->with('success', 'Producto creado correctamente');
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'descripcion'  => 'required|string|max:255',
            'codigo'       => 'required|string|max:50',
            'precio_venta' => 'required|numeric|min:0',
            'precio_compra'=> 'nullable|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
            'codigo_barras'=> 'nullable|string|max:100',
            'categoria_id' => 'nullable|exists:categorias_minimarket,id',
        ]);

        $producto->update([
            'categoria_id'  => $request->categoria_id,
            'descripcion'   => $request->descripcion,
            'codigo'        => $request->codigo,
            'precio_venta'  => $request->precio_venta,
            'precio_compra' => $request->precio_compra ?? 0,
            'stock_minimo'  => $request->stock_minimo ?? 0,
            'codigo_barras' => $request->codigo_barras,
        ]);

        return redirect()->route('minimarket.productos')->with('success', 'Producto actualizado correctamente');
    }

    public function ajustarStock(Request $request, Producto $producto)
    {
        $request->validate([
            'tipo'     => 'required|in:entrada,salida,ajuste',
            'cantidad' => 'required|numeric|min:1',
        ]);

        if ($request->tipo === 'entrada') {
            $producto->increment('stock_actual', $request->cantidad);
        } elseif ($request->tipo === 'salida') {
            $producto->decrement('stock_actual', $request->cantidad);
        } else {
            $producto->update(['stock_actual' => $request->cantidad]);
        }

        return redirect()->route('minimarket.productos')->with('success', 'Stock ajustado correctamente');
    }

    public function destroy(Producto $producto)
    {
        $producto->update(['activo' => false]);
        return redirect()->route('minimarket.productos')->with('success', 'Producto desactivado correctamente');
    }
}
