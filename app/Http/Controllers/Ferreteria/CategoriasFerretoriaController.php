<?php

namespace App\Http\Controllers\Ferreteria;

use App\Http\Controllers\Controller;
use App\Models\CategoriaMinimarket as Categoria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoriasFerretoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::where('empresa_id', auth()->user()->empresa_id)
            ->withCount('productos')
            ->orderBy('nombre')
            ->get();
        return Inertia::render('Ferreteria/Categorias', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate(['nombre' => 'required|string|max:100']);
        Categoria::create([
            'empresa_id' => auth()->user()->empresa_id,
            'nombre'     => $request->nombre,
            'icono'      => $request->icono ?? '🔧',
            'color'      => $request->color ?? '#14B8A6',
        ]);
        return back()->with('success', 'Categoría creada');
    }

    public function update(Request $request, Categoria $categoria)
    {
        $categoria->update([
            'nombre' => $request->nombre,
            'icono'  => $request->icono,
            'color'  => $request->color,
        ]);
        return back()->with('success', 'Categoría actualizada');
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->delete();
        return back()->with('success', 'Categoría eliminada');
    }
}
