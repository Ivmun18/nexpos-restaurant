<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use App\Helpers\EmpresaHelper;
use Inertia\Inertia;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::orderBy('descripcion')->paginate(20);

        return Inertia::render('Productos/Index', [
            'productos' => $productos,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo'        => 'required|max:50',
            'descripcion'   => 'required|max:500',
            'precio_venta'  => 'required|numeric|min:0',
            'unidad_medida' => 'required|max:10',
        ]);

        Producto::create([
            'empresa_id'          => EmpresaHelper::id(),
            'codigo'              => strtoupper($request->codigo),
            'descripcion'         => strtoupper($request->descripcion),
            'tipo'                => $request->tipo ?? 'producto',
            'unidad_medida'       => $request->unidad_medida,
            'precio_venta'        => $request->precio_venta,
            'precio_compra'       => $request->precio_compra,
            'afecto_igv'          => $request->afecto_igv ?? 1,
            'tipo_afectacion_igv' => $request->tipo_afectacion_igv ?? '10',
            'controla_stock'      => $request->controla_stock ?? 1,
            'stock_minimo'        => $request->stock_minimo ?? 0,
	    'stock_actual' 	  => $request->stock_actual ?? 0,
	    'codigo_barras' => $request->codigo_barras,
            'activo'              => 1,
        ]);

        return back()->with('success', 'Producto registrado correctamente.');
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'descripcion'  => 'required|max:500',
            'precio_venta' => 'required|numeric|min:0',
        ]);

        $producto->update([
            'descripcion'         => strtoupper($request->descripcion),
            'unidad_medida'       => $request->unidad_medida,
            'precio_venta'        => $request->precio_venta,
            'precio_compra'       => $request->precio_compra,
            'tipo_afectacion_igv' => $request->tipo_afectacion_igv,
            'afecto_igv'          => $request->tipo_afectacion_igv === '10' ? 1 : 0,
            'stock_actual'        => $request->stock_actual ?? 0,
            'stock_minimo'        => $request->stock_minimo ?? 0,
	    'codigo_barras' => $request->codigo_barras,
            'activo'              => $request->activo ?? 1,
        ]);

        return back()->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return back()->with('success', 'Producto eliminado correctamente.');
    }
}
