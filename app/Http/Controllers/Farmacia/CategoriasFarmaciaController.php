<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use App\Models\CategoriaMinimarket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoriasFarmaciaController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;

        $categorias = CategoriaMinimarket::where('empresa_id', $empresa_id)
            ->withCount('productos')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Farmacia/Categorias', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'icono'  => 'nullable|string|max:10',
            'color'  => 'nullable|string|max:20',
        ]);

        CategoriaMinimarket::create([
            'empresa_id' => auth()->user()->empresa_id,
            'nombre'     => $request->nombre,
            'icono'      => $request->icono ?? '📦',
            'color'      => $request->color ?? '#14B8A6',
            'activo'     => true,
        ]);

        return back()->with('success', 'Categoría creada correctamente.');
    }

    public function update(Request $request, CategoriaMinimarket $categoria)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'icono'  => 'nullable|string|max:10',
            'color'  => 'nullable|string|max:20',
        ]);

        $categoria->update([
            'nombre' => $request->nombre,
            'icono'  => $request->icono ?? '📦',
            'color'  => $request->color ?? '#14B8A6',
            'activo' => $request->activo ?? true,
        ]);

        return back()->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(CategoriaMinimarket $categoria)
    {
        if ($categoria->productos()->count() > 0) {
            return back()->with('error', 'No puedes eliminar una categoría con productos asignados.');
        }

        $categoria->delete();
        return back()->with('success', 'Categoría eliminada correctamente.');
    }
}
