<?php

namespace App\Http\Controllers\Ferreteria;

use App\Http\Controllers\Controller;
use App\Models\OrdenTrabajo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrdenesFerretoriaController extends Controller
{
    public function index()
    {
        $ordenes = OrdenTrabajo::where('empresa_id', auth()->user()->empresa_id)
            ->orderByDesc('created_at')->get();
        return Inertia::render('Ferreteria/Ordenes', compact('ordenes'));
    }

    public function store(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $numero = 'OT-' . str_pad(OrdenTrabajo::where('empresa_id', $empresa_id)->count() + 1, 4, '0', STR_PAD_LEFT);
        OrdenTrabajo::create(array_merge($request->all(), [
            'empresa_id' => $empresa_id,
            'usuario_id' => auth()->id(),
            'numero'     => $numero,
        ]));
        return back()->with('success', 'Orden creada');
    }

    public function update(Request $request, OrdenTrabajo $orden)
    {
        $orden->update($request->all());
        return back()->with('success', 'Orden actualizada');
    }

    public function cambiarEstado(Request $request, OrdenTrabajo $orden)
    {
        $orden->update(['estado' => $request->estado]);
        return back()->with('success', 'Estado actualizado');
    }

    public function destroy(OrdenTrabajo $orden)
    {
        $orden->delete();
        return back()->with('success', 'Orden eliminada');
    }
}
