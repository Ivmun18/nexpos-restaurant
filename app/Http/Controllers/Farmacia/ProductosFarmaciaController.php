<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\CategoriaMinimarket as Categoria;
use Illuminate\Http\Request;
use App\Models\AuditoriaLog;
use Inertia\Inertia;
use Carbon\Carbon;

class ProductosFarmaciaController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;
        $productos  = Producto::where('empresa_id', $empresa_id)
            ->where('activo', true)
            ->with('categoria')
            ->orderBy('descripcion')
            ->get();
        $categorias = Categoria::where('empresa_id', $empresa_id)->orderBy('nombre')->get();

        return Inertia::render('Farmacia/Productos', compact('productos', 'categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion'      => 'required|string|max:255',
            'precio_venta'     => 'required|numeric|min:0',
            'stock_actual'     => 'required|integer|min:0',
            'fecha_vencimiento'=> 'nullable|date',
        ]);

        $producto = Producto::create([
            ...$request->all(),
            'empresa_id' => auth()->user()->empresa_id,
            'activo'     => true,
        ]);

        AuditoriaLog::registrar(
            'producto',
            'creado',
            'producto',
            $producto->id,
            $producto->descripcion,
            null,
            $request->only(['descripcion', 'precio_venta', 'precio_compra', 'stock_actual', 'codigo_barras']),
            'Producto creado por ' . auth()->user()->name
        );

        return redirect()->back()->with('success', 'Producto creado correctamente');
    }

    public function update(Request $request, Producto $producto)
    {
        $datosAntes = $producto->only(['descripcion', 'precio_venta', 'precio_compra', 'stock_actual', 'lote', 'fecha_vencimiento', 'codigo_barras']);
        $producto->update($request->all());
        
        // Detectar cambio importante de precio
        $severidad = 'info';
        $accion = 'editado';
        if (isset($datosAntes['precio_venta']) && (float)$datosAntes['precio_venta'] !== (float)$producto->precio_venta) {
            $accion = 'precio_cambiado';
            $diferencia = abs((float)$producto->precio_venta - (float)$datosAntes['precio_venta']);
            $porcentaje = $datosAntes['precio_venta'] > 0 ? ($diferencia / $datosAntes['precio_venta'] * 100) : 0;
            if ($porcentaje > 30) $severidad = 'warning';
        }
        
        AuditoriaLog::registrar(
            'producto',
            $accion,
            'producto',
            $producto->id,
            $producto->descripcion,
            $datosAntes,
            $producto->only(['descripcion', 'precio_venta', 'precio_compra', 'stock_actual', 'lote', 'fecha_vencimiento']),
            'Producto editado por ' . auth()->user()->name,
            $severidad
        );
        
        return redirect()->back()->with('success', 'Producto actualizado');
    }

    public function destroy(Producto $producto)
    {
        AuditoriaLog::registrar(
            'producto',
            'eliminado',
            'producto',
            $producto->id,
            $producto->descripcion,
            $producto->only(['descripcion', 'precio_venta', 'stock_actual', 'lote']),
            null,
            'Producto eliminado (soft delete) por ' . auth()->user()->name,
            'warning'
        );
        
        $producto->update(['activo' => false]);
        return redirect()->back()->with('success', 'Producto eliminado');
    }

    public function actualizarStock(Request $request, Producto $producto)
    {
        $stockAnterior = $producto->stock_actual;
        $producto->increment('stock_actual', $request->cantidad);
        
        AuditoriaLog::registrar(
            'inventario',
            'ajuste_stock',
            'producto',
            $producto->id,
            $producto->descripcion,
            ['stock' => $stockAnterior],
            ['stock' => $producto->fresh()->stock_actual],
            'Stock ajustado +' . $request->cantidad . ' por ' . auth()->user()->name
        );
        
        return redirect()->back()->with('success', 'Stock actualizado');
    }

    public function vencimientos()
    {
        $empresa_id = auth()->user()->empresa_id;
        $hoy        = Carbon::today();

        $productos = Producto::where('empresa_id', $empresa_id)
            ->where('activo', true)
            ->whereNotNull('fecha_vencimiento')
            ->get();

        $vencidos    = $productos->filter(fn($p) => Carbon::parse($p->fecha_vencimiento)->lt($hoy))->values();
        $porVencer   = $productos->filter(fn($p) => 
            Carbon::parse($p->fecha_vencimiento)->gte($hoy) && 
            Carbon::parse($p->fecha_vencimiento)->lte($hoy->copy()->addDays(30))
        )->values();
        $porVencer90 = $productos->filter(fn($p) => 
            Carbon::parse($p->fecha_vencimiento)->gt($hoy->copy()->addDays(30)) && 
            Carbon::parse($p->fecha_vencimiento)->lte($hoy->copy()->addDays(90))
        )->values();

        $stockBajo = Producto::where('empresa_id', $empresa_id)
            ->where('activo', true)
            ->whereNotNull('stock_minimo')
            ->whereRaw('stock_actual <= stock_minimo')
            ->orderBy('stock_actual')
            ->get();

        return Inertia::render('Farmacia/Vencimientos', compact('vencidos', 'porVencer', 'porVencer90', 'stockBajo'));
    }
}
