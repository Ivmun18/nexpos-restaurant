<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use App\Models\MenuProducto;
use App\Models\Insumo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RecetaController extends Controller
{
    public function index()
    {
        $productos = MenuProducto::with(['recetas.insumo'])
            ->where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('nombre')
            ->get()
            ->map(fn($p) => [
                'id'      => $p->id,
                'nombre'  => $p->nombre,
                'precio'  => $p->precio,
                'recetas' => $p->recetas->map(fn($r) => [
                    'id'            => $r->id,
                    'insumo_id'     => $r->insumo_id,
                    'insumo_nombre' => $r->insumo->nombre,
                    'unidad'        => $r->insumo->unidad_medida,
                    'cantidad'      => $r->cantidad,
                ]),
            ]);

        $insumos = Insumo::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'unidad_medida', 'stock_actual']);

        return Inertia::render('Recetas/Index', [
            'productos' => $productos,
            'insumos'   => $insumos,
        ]);
    }

    public function store(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $request->validate([
            'menu_producto_id' => ['required', Rule::exists('menu_productos', 'id')->where('empresa_id', $empresaId)],
            'insumo_id'        => ['required', Rule::exists('insumos', 'id')->where('empresa_id', $empresaId)],
            'cantidad'         => 'required|numeric|min:0.0001',
        ]);

        Receta::updateOrCreate(
            ['menu_producto_id' => $request->menu_producto_id, 'insumo_id' => $request->insumo_id],
            ['cantidad' => $request->cantidad]
        );

        return back()->with('success', 'Insumo agregado a la receta.');
    }

    public function destroy(Receta $receta)
    {
        if ($receta->menuProducto->empresa_id !== auth()->user()->empresa_id) {
            abort(403);
        }

        $receta->delete();
        return back()->with('success', 'Insumo eliminado de la receta.');
    }
}
