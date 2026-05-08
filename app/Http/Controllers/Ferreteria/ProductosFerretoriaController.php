<?php

namespace App\Http\Controllers\Ferreteria;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\CategoriaMinimarket as Categoria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductosFerretoriaController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;
        $productos = Producto::where('empresa_id', $empresa_id)->with('categoria')->orderBy('descripcion')->get();
        $categorias = Categoria::where('empresa_id', $empresa_id)->orderBy('nombre')->get();
        return Inertia::render('Ferreteria/Productos', compact('productos', 'categorias'));
    }

    public function store(Request $request)
    {
        $request->validate(['descripcion' => 'required', 'precio_venta' => 'required|numeric']);
        Producto::create([
            'empresa_id'    => auth()->user()->empresa_id,
            'descripcion'   => $request->descripcion,
            'codigo'        => $request->codigo,
            'codigo_barras' => $request->codigo_barras,
            'unidad_medida' => $request->unidad_medida ?? 'UND',
            'categoria_id'  => $request->categoria_id ?: null,
            'precio_compra' => $request->precio_compra ?? 0,
            'precio_venta'  => $request->precio_venta,
            'stock_actual'  => $request->stock_actual ?? 0,
            'stock_minimo'  => $request->stock_minimo ?? 5,
            'marca'         => $request->marca,
        ]);
        return back()->with('success', 'Producto creado');
    }

    public function update(Request $request, Producto $producto)
    {
        $producto->update([
            'descripcion'   => $request->descripcion,
            'codigo'        => $request->codigo,
            'codigo_barras' => $request->codigo_barras,
            'unidad_medida' => $request->unidad_medida ?? 'UND',
            'categoria_id'  => $request->categoria_id ?: null,
            'precio_compra' => $request->precio_compra ?? 0,
            'precio_venta'  => $request->precio_venta,
            'stock_minimo'  => $request->stock_minimo ?? 5,
            'marca'         => $request->marca,
        ]);
        return back()->with('success', 'Producto actualizado');
    }

    public function ajustarStock(Request $request, Producto $producto)
    {
        $cantidad = (int) $request->cantidad;
        if ($request->tipo === 'entrada') {
            $producto->increment('stock_actual', $cantidad);
        } else {
            $producto->decrement('stock_actual', $cantidad);
        }
        return back()->with('success', 'Stock ajustado');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return back()->with('success', 'Producto eliminado');
    }
}
