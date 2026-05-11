<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\CategoriaMinimarket as Categoria;
use Illuminate\Http\Request;
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

        Producto::create([
            ...$request->all(),
            'empresa_id' => auth()->user()->empresa_id,
            'activo'     => true,
        ]);

        return redirect()->back()->with('success', 'Producto creado correctamente');
    }

    public function update(Request $request, Producto $producto)
    {
        $producto->update($request->all());
        return redirect()->back()->with('success', 'Producto actualizado');
    }

    public function destroy(Producto $producto)
    {
        $producto->update(['activo' => false]);
        return redirect()->back()->with('success', 'Producto eliminado');
    }

    public function actualizarStock(Request $request, Producto $producto)
    {
        $producto->increment('stock_actual', $request->cantidad);
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
