<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use App\Models\FarmaciaReceta;
use App\Models\Producto;
use App\Helpers\EmpresaHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecetasFarmaciaController extends Controller
{
    public function index(Request $request)
    {
        $query = FarmaciaReceta::where('empresa_id', EmpresaHelper::id())
            ->orderBy('created_at', 'desc');

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function($q) use ($b) {
                $q->where('paciente_nombre', 'like', "%$b%")
                  ->orWhere('paciente_dni', 'like', "%$b%")
                  ->orWhere('medico', 'like', "%$b%")
                  ->orWhere('numero_receta', 'like', "%$b%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $recetas = $query->paginate(20)->withQueryString();

        $productos = Producto::where('empresa_id', EmpresaHelper::id())
            ->where('activo', true)
            ->where('requiere_receta', true)
            ->orderBy('descripcion')
            ->get(['id', 'descripcion', 'codigo_barras', 'stock_actual', 'precio_venta']);

        return Inertia::render('Farmacia/Recetas', [
            'recetas'   => $recetas,
            'productos' => $productos,
            'filtros'   => $request->only(['buscar', 'estado']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'paciente_nombre' => 'required|string|max:200',
            'paciente_dni'    => 'nullable|string|max:15',
            'fecha'           => 'required|date',
            'medico'          => 'nullable|string|max:200',
            'especialidad'    => 'nullable|string|max:100',
            'establecimiento' => 'nullable|string|max:200',
            'numero_receta'   => 'nullable|string|max:50',
            'items'           => 'required|array|min:1',
            'items.*.producto_id'  => 'required|integer',
            'items.*.descripcion'  => 'required|string',
            'items.*.cantidad'     => 'required|numeric|min:1',
        ]);

        FarmaciaReceta::create([
            'empresa_id'      => EmpresaHelper::id(),
            'venta_id'        => $request->venta_id ?? null,
            'numero_receta'   => $request->numero_receta,
            'fecha'           => $request->fecha,
            'medico'          => $request->medico,
            'especialidad'    => $request->especialidad,
            'establecimiento' => $request->establecimiento,
            'paciente_nombre' => $request->paciente_nombre,
            'paciente_dni'    => $request->paciente_dni,
            'items'           => $request->items,
            'estado'          => 'despachada',
            'observaciones'   => $request->observaciones,
            'usuario_id'      => auth()->id(),
        ]);

        return back()->with('success', 'Receta registrada correctamente.');
    }

    public function update(Request $request, FarmaciaReceta $receta)
    {
        if ($receta->empresa_id !== EmpresaHelper::id()) abort(403);

        $receta->update([
            'estado'       => $request->estado,
            'observaciones'=> $request->observaciones,
        ]);

        return back()->with('success', 'Receta actualizada.');
    }

    public function destroy(FarmaciaReceta $receta)
    {
        if ($receta->empresa_id !== EmpresaHelper::id()) abort(403);
        $receta->delete();
        return back()->with('success', 'Receta eliminada.');
    }
}
