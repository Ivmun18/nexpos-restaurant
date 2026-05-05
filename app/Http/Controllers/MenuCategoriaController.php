<?php

namespace App\Http\Controllers;

use App\Models\MenuCategoria;
use App\Models\MenuProducto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuCategoriaController extends Controller
{
    public function index(): Response
    {
        $categorias = MenuCategoria::with('productosActivos')
            ->orderBy('orden')
            ->get();

        return Inertia::render('Menu/Index', [
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'icono'       => 'nullable|string|max:50',
            'color'       => 'nullable|string|max:7',
            'orden'       => 'integer|min:0',
            'activo'      => 'boolean',
        ]);

        MenuCategoria::create($validated);

        return redirect()->back()->with('success', 'Categoría creada.');
    }

    public function update(Request $request, MenuCategoria $menuCategoria)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'icono'       => 'nullable|string|max:50',
            'color'       => 'nullable|string|max:7',
            'orden'       => 'integer|min:0',
            'activo'      => 'boolean',
        ]);

        $menuCategoria->update($validated);

        return redirect()->back()->with('success', 'Categoría actualizada.');
    }

    public function destroy(MenuCategoria $menuCategoria)
    {
        $menuCategoria->delete();
        return redirect()->back()->with('success', 'Categoría eliminada.');
    }

    public function apiCarta()
    {
        $categorias = MenuCategoria::with(['productosActivos' => function ($q) {
            $q->where('disponible', true);
        }])->activas()->get();

        return response()->json($categorias);
    }
}