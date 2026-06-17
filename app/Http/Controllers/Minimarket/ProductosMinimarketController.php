<?php

namespace App\Http\Controllers\Minimarket;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\CategoriaMinimarket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductosMinimarketController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;

        $productos = Producto::where('empresa_id', $empresa_id)
            ->with(['categoria', 'presentaciones' => fn($q) => $q->where('activo', true)])
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

        $producto = Producto::create([
            'empresa_id'    => $empresa_id,
            'categoria_id'  => $request->categoria_id,
            'descripcion'   => $request->descripcion,
            'codigo'        => $request->filled('codigo') ? $request->codigo : 'P' . str_pad(Producto::where('empresa_id', $empresa_id)->count() + 1, 3, '0', STR_PAD_LEFT),
            'precio_venta'  => $request->precio_venta,
            'precio_compra' => $request->precio_compra ?? 0,
            'stock_actual'  => $request->stock_actual ?? 0,
            'stock_minimo'  => $request->stock_minimo ?? 0,
            'codigo_barras' => $request->codigo_barras,
            'activo'        => true,
            'controla_stock'=> true,
        ]);

        if ($request->has('presentaciones') && is_array($request->presentaciones)) {
            $tieneDefault = false;
            foreach ($request->presentaciones as $p) {
                if (empty($p['nombre']) || empty($p['factor_conversion']) || !isset($p['precio_venta'])) {
                    continue;
                }
                $esDefault = !empty($p['es_default']) && !$tieneDefault;
                if ($esDefault) $tieneDefault = true;

                \App\Models\ProductoPresentacion::create([
                    'producto_id'        => $producto->id,
                    'nombre'             => $p['nombre'],
                    'unidad_sunat'       => $p['unidad_sunat'] ?? 'NIU',
                    'factor_conversion'  => $p['factor_conversion'],
                    'precio_venta'       => $p['precio_venta'],
                    'codigo_barras'      => $p['codigo_barras'] ?? null,
                    'es_default'         => $esDefault,
                ]);
            }
        }

        return redirect()->route('minimarket.productos')->with('success', 'Producto creado correctamente');
    }

    public function update(Request $request, Producto $producto)
    {
        abort_if($producto->empresa_id !== auth()->user()->empresa_id, 403);

        $request->validate([
            'descripcion'  => 'required|string|max:255',
            'codigo'       => 'nullable|string|max:50',
            'precio_venta' => 'required|numeric|min:0',
            'precio_compra'=> 'nullable|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
            'codigo_barras'=> 'nullable|string|max:100',
            'categoria_id' => 'nullable|exists:categorias_minimarket,id',
        ]);

        $producto->update([
            'categoria_id'  => $request->categoria_id,
            'descripcion'   => $request->descripcion,
            'codigo'        => $request->filled('codigo') ? $request->codigo : ($producto->codigo ?: 'P' . str_pad($producto->id, 3, '0', STR_PAD_LEFT)),
            'precio_venta'  => $request->precio_venta,
            'precio_compra' => $request->precio_compra ?? 0,
            'stock_minimo'  => $request->stock_minimo ?? 0,
            'codigo_barras' => $request->codigo_barras,
        ]);

        return redirect()->route('minimarket.productos')->with('success', 'Producto actualizado correctamente');
    }

    public function ajustarStock(Request $request, Producto $producto)
    {
        abort_if($producto->empresa_id !== auth()->user()->empresa_id, 403);

        $request->validate([
            'tipo'         => 'required|in:entrada,salida,ajuste',
            'cantidad'     => 'required|numeric|min:0.01',
            'observaciones'=> 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $producto) {
            $producto->refresh();
            $stockAnterior = $producto->stock_actual;

            if ($request->tipo === 'entrada') {
                $stockNuevo = $stockAnterior + $request->cantidad;
            } elseif ($request->tipo === 'salida') {
                if ($stockAnterior < $request->cantidad) {
                    throw new \Exception("Stock insuficiente. Disponible: {$stockAnterior}, solicitado: {$request->cantidad}");
                }
                $stockNuevo = $stockAnterior - $request->cantidad;
            } else {
                $stockNuevo = $request->cantidad;
            }

            $producto->update(['stock_actual' => $stockNuevo]);

            \App\Models\InventarioMovimiento::create([
                'empresa_id'     => $producto->empresa_id,
                'producto_id'    => $producto->id,
                'usuario_id'     => auth()->id(),
                'tipo'           => $request->tipo,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo'    => $stockNuevo,
                'diferencia'     => $stockNuevo - $stockAnterior,
                'observaciones'  => $request->observaciones,
            ]);
        });

        return redirect()->route('minimarket.productos')->with('success', 'Stock ajustado correctamente');
    }

    public function destroy(Producto $producto)
    {
        abort_if($producto->empresa_id !== auth()->user()->empresa_id, 403);

        $producto->update(['activo' => false]);
        return redirect()->route('minimarket.productos')->with('success', 'Producto desactivado correctamente');
    }

    public function storePresentacion(Request $request, Producto $producto)
    {
        abort_if($producto->empresa_id !== auth()->user()->empresa_id, 403);

        $request->validate([
            'nombre'            => 'required|string|max:100',
            'unidad_sunat'      => 'required|string|max:10',
            'factor_conversion' => 'required|numeric|min:0.0001',
            'precio_venta'      => 'required|numeric|min:0',
            'codigo_barras'     => 'nullable|string|max:100',
            'es_default'        => 'nullable|boolean',
        ]);

        if ($request->boolean('es_default')) {
            \App\Models\ProductoPresentacion::where('producto_id', $producto->id)
                ->update(['es_default' => false]);
        }

        \App\Models\ProductoPresentacion::create([
            'producto_id'        => $producto->id,
            'nombre'             => $request->nombre,
            'unidad_sunat'       => $request->unidad_sunat,
            'factor_conversion'  => $request->factor_conversion,
            'precio_venta'       => $request->precio_venta,
            'codigo_barras'      => $request->codigo_barras,
            'es_default'         => $request->boolean('es_default'),
            'activo'             => true,
        ]);

        return redirect()->route('minimarket.productos')->with('success', 'Presentacion agregada correctamente');
    }

    public function updatePresentacion(Request $request, \App\Models\ProductoPresentacion $presentacion)
    {
        $producto = $presentacion->producto;
        abort_if(!$producto || $producto->empresa_id !== auth()->user()->empresa_id, 403);

        $request->validate([
            'nombre'            => 'required|string|max:100',
            'unidad_sunat'      => 'required|string|max:10',
            'factor_conversion' => 'required|numeric|min:0.0001',
            'precio_venta'      => 'required|numeric|min:0',
            'codigo_barras'     => 'nullable|string|max:100',
            'es_default'        => 'nullable|boolean',
            'activo'            => 'nullable|boolean',
        ]);

        if ($request->boolean('es_default')) {
            \App\Models\ProductoPresentacion::where('producto_id', $producto->id)
                ->where('id', '!=', $presentacion->id)
                ->update(['es_default' => false]);
        }

        $presentacion->update([
            'nombre'             => $request->nombre,
            'unidad_sunat'       => $request->unidad_sunat,
            'factor_conversion'  => $request->factor_conversion,
            'precio_venta'       => $request->precio_venta,
            'codigo_barras'      => $request->codigo_barras,
            'es_default'         => $request->boolean('es_default'),
            'activo'             => $request->has('activo') ? $request->boolean('activo') : $presentacion->activo,
        ]);

        return redirect()->route('minimarket.productos')->with('success', 'Presentacion actualizada correctamente');
    }

    public function destroyPresentacion(\App\Models\ProductoPresentacion $presentacion)
    {
        $producto = $presentacion->producto;
        abort_if(!$producto || $producto->empresa_id !== auth()->user()->empresa_id, 403);

        $presentacion->delete();

        return redirect()->route('minimarket.productos')->with('success', 'Presentacion eliminada correctamente');
    }
}