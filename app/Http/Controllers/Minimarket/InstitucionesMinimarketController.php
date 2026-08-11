<?php

namespace App\Http\Controllers\Minimarket;

use App\Http\Controllers\Controller;
use App\Models\InstitucionMinimarket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstitucionesMinimarketController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;

        $instituciones = InstitucionMinimarket::where('empresa_id', $empresa_id)
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Minimarket/Instituciones', [
            'instituciones' => $instituciones,
        ]);
    }

    public function store(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;

        $request->validate([
            'nombre'             => 'required|string|max:150',
            'porcentaje_recargo' => 'required|numeric|min:0|max:100',
        ]);

        InstitucionMinimarket::create([
            'empresa_id'         => $empresa_id,
            'nombre'             => $request->nombre,
            'porcentaje_recargo' => $request->porcentaje_recargo,
            'activo'             => true,
        ]);

        return redirect()->route('minimarket.instituciones')->with('success', 'Institucion creada correctamente');
    }

    public function update(Request $request, InstitucionMinimarket $institucion)
    {
        abort_if($institucion->empresa_id !== auth()->user()->empresa_id, 403);

        $request->validate([
            'nombre'             => 'required|string|max:150',
            'porcentaje_recargo' => 'required|numeric|min:0|max:100',
            'activo'             => 'nullable|boolean',
        ]);

        $institucion->update([
            'nombre'             => $request->nombre,
            'porcentaje_recargo' => $request->porcentaje_recargo,
            'activo'             => $request->has('activo') ? $request->boolean('activo') : $institucion->activo,
        ]);

        return redirect()->route('minimarket.instituciones')->with('success', 'Institucion actualizada correctamente');
    }

    public function destroy(InstitucionMinimarket $institucion)
    {
        abort_if($institucion->empresa_id !== auth()->user()->empresa_id, 403);
        $institucion->delete();

        return redirect()->route('minimarket.instituciones')->with('success', 'Institucion eliminada correctamente');
    }
}
