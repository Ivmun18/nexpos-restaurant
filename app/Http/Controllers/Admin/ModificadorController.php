<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\EmpresaHelper;
use App\Http\Controllers\Controller;
use App\Models\MenuCategoria;
use App\Models\Modificador;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModificadorController extends Controller
{
    public function index()
    {
        $empresaId = EmpresaHelper::id();

        $modificadores = Modificador::deEmpresa($empresaId)
            ->with('categoria:id,nombre')
            ->orderBy('nombre')
            ->get();

        $categorias = MenuCategoria::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->orderBy('orden')
            ->get(['id', 'nombre']);

        return Inertia::render('Admin/Modificadores/Index', [
            'modificadores' => $modificadores,
            'categorias'    => $categorias,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:100',
            'categoria_id' => 'nullable|exists:menu_categorias,id',
        ]);

        Modificador::create([
            'empresa_id'   => EmpresaHelper::id(),
            'categoria_id' => $request->categoria_id,
            'nombre'       => $request->nombre,
            'activo'       => true,
        ]);

        return back()->with('success', 'Modificador creado correctamente.');
    }

    public function update(Request $request, Modificador $modificador)
    {
        $request->validate([
            'nombre'       => 'required|string|max:100',
            'categoria_id' => 'nullable|exists:menu_categorias,id',
            'activo'       => 'boolean',
        ]);

        $modificador->update($request->only(['nombre', 'categoria_id', 'activo']));

        return back()->with('success', 'Modificador actualizado correctamente.');
    }

    public function destroy(Modificador $modificador)
    {
        $modificador->delete();

        return back()->with('success', 'Modificador eliminado.');
    }

    /**
     * Modificadores activos de la empresa del usuario autenticado.
     * Sin categoria_id devuelve todos; con categoria_id incluye ademas
     * los que aplican a todas las categorias (categoria_id nulo).
     */
    public function api(Request $request)
    {
        $empresaId = EmpresaHelper::id();
        $categoriaId = $request->input('categoria_id');

        $query = Modificador::deEmpresa($empresaId)->where('activo', true);

        if ($categoriaId) {
            $query->where(function ($q) use ($categoriaId) {
                $q->where('categoria_id', $categoriaId)->orWhereNull('categoria_id');
            });
        }

        return response()->json(
            $query->orderBy('nombre')->get(['id', 'categoria_id', 'nombre'])
        );
    }
}
