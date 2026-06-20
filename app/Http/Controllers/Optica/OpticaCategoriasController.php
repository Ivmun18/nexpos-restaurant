<?php
namespace App\Http\Controllers\Optica;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpticaCategoria;
use Inertia\Inertia;

class OpticaCategoriasController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;
        $categorias = OpticaCategoria::where('empresa_id', $empresa_id)
            ->withCount('productos')->orderBy('nombre')->get();
        return Inertia::render('Optica/Categorias/Index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:80',
            'color'  => 'nullable|string|max:30',
        ]);
        $data['empresa_id'] = auth()->user()->empresa_id;
        $data['activo'] = true;
        OpticaCategoria::create($data);
        return back()->with('success', 'Categoría creada.');
    }

    public function update(Request $request, $id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $cat = OpticaCategoria::where('empresa_id', $empresa_id)->findOrFail($id);
        $data = $request->validate([
            'nombre' => 'required|string|max:80',
            'color'  => 'nullable|string|max:30',
            'activo' => 'boolean',
        ]);
        $cat->update($data);
        return back()->with('success', 'Categoría actualizada.');
    }

    public function destroy($id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $cat = OpticaCategoria::where('empresa_id', $empresa_id)->findOrFail($id);
        $cat->delete();
        return back()->with('success', 'Categoría eliminada.');
    }
}
