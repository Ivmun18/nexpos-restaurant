<?php

namespace App\Http\Controllers;

use App\Models\MenuProducto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuProductoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_categoria_id'  => ['required', Rule::exists('menu_categorias', 'id')->where('empresa_id', auth()->user()->empresa_id)],
            'nombre'             => 'required|string|max:150',
            'descripcion'        => 'nullable|string',
            'precio'             => 'required|numeric|min:0',
            'disponible'         => 'boolean',
            'activo'             => 'boolean',
            'orden'              => 'integer|min:0',
            'tiempo_preparacion' => 'integer|min:0',
        ]);

        $validated['empresa_id'] = auth()->user()->empresa_id;
        MenuProducto::create($validated);

        return redirect()->back()->with('success', 'Producto agregado.');
    }

    public function update(Request $request, MenuProducto $menuProducto)
    {
        if ($menuProducto->empresa_id !== auth()->user()->empresa_id) {
            abort(403);
        }

        $validated = $request->validate([
            'menu_categoria_id'  => ['required', Rule::exists('menu_categorias', 'id')->where('empresa_id', auth()->user()->empresa_id)],
            'nombre'             => 'required|string|max:150',
            'descripcion'        => 'nullable|string',
            'precio'             => 'required|numeric|min:0',
            'disponible'         => 'boolean',
            'activo'             => 'boolean',
            'orden'              => 'integer|min:0',
            'tiempo_preparacion' => 'integer|min:0',
        ]);

        $menuProducto->update($validated);

        return redirect()->back()->with('success', 'Producto actualizado.');
    }

    public function destroy(MenuProducto $menuProducto)
    {
        if ($menuProducto->empresa_id !== auth()->user()->empresa_id) {
            abort(403);
        }

        $menuProducto->delete();
        return redirect()->back()->with('success', 'Producto eliminado.');
    }

    public function toggleDisponible(MenuProducto $menuProducto)
    {
        if ($menuProducto->empresa_id !== auth()->user()->empresa_id) {
            abort(403);
        }

        $menuProducto->update(['disponible' => !$menuProducto->disponible]);
        return response()->json(['disponible' => $menuProducto->disponible]);
    }
}