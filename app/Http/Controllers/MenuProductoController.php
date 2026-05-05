<?php

namespace App\Http\Controllers;

use App\Models\MenuProducto;
use Illuminate\Http\Request;

class MenuProductoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_categoria_id'  => 'required|exists:menu_categorias,id',
            'nombre'             => 'required|string|max:150',
            'descripcion'        => 'nullable|string',
            'precio'             => 'required|numeric|min:0',
            'disponible'         => 'boolean',
            'activo'             => 'boolean',
            'orden'              => 'integer|min:0',
            'tiempo_preparacion' => 'integer|min:0',
        ]);

        MenuProducto::create($validated);

        return redirect()->back()->with('success', 'Producto agregado.');
    }

    public function update(Request $request, MenuProducto $menuProducto)
    {
        $validated = $request->validate([
            'menu_categoria_id'  => 'required|exists:menu_categorias,id',
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
        $menuProducto->delete();
        return redirect()->back()->with('success', 'Producto eliminado.');
    }

    public function toggleDisponible(MenuProducto $menuProducto)
    {
        $menuProducto->update(['disponible' => !$menuProducto->disponible]);
        return response()->json(['disponible' => $menuProducto->disponible]);
    }
}