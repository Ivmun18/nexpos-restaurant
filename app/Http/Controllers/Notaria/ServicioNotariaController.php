<?php

namespace App\Http\Controllers\Notaria;

use App\Http\Controllers\Controller;
use App\Models\NotariaServicio;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServicioNotariaController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;
        $servicios = NotariaServicio::where('empresa_id', $empresaId)
            ->orderBy('nombre')->get();
        return Inertia::render('Notaria/Servicios/Index', compact('servicios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
        ]);
        NotariaServicio::create([
            'empresa_id' => auth()->user()->empresa_id,
            'nombre'     => $request->nombre,
            'precio'     => $request->precio,
            'activo'     => true,
        ]);
        return back()->with('success', 'Servicio creado.');
    }

    public function update(Request $request, NotariaServicio $servicio)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'activo' => 'boolean',
        ]);
        $servicio->update($request->only('nombre', 'precio', 'activo'));
        return back()->with('success', 'Servicio actualizado.');
    }

    public function destroy(NotariaServicio $servicio)
    {
        $servicio->delete();
        return back()->with('success', 'Servicio eliminado.');
    }

    public function lista()
    {
        $empresaId = auth()->user()->empresa_id;
        $servicios = NotariaServicio::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'precio']);
        return response()->json($servicios);
    }
}
