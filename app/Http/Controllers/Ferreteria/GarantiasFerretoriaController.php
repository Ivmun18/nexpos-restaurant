<?php

namespace App\Http\Controllers\Ferreteria;

use App\Http\Controllers\Controller;
use App\Models\Garantia;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GarantiasFerretoriaController extends Controller
{
    public function index()
    {
        $garantias = Garantia::where('empresa_id', auth()->user()->empresa_id)
            ->orderByDesc('created_at')->get();
        return Inertia::render('Ferreteria/Garantias', compact('garantias'));
    }

    public function store(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $numero = 'GAR-' . str_pad(Garantia::where('empresa_id', $empresa_id)->count() + 1, 4, '0', STR_PAD_LEFT);
        Garantia::create(array_merge($request->all(), [
            'empresa_id' => $empresa_id,
            'numero'     => $numero,
        ]));
        return back()->with('success', 'Garantía creada');
    }

    public function update(Request $request, Garantia $garantia)
    {
        $garantia->update($request->all());
        return back()->with('success', 'Garantía actualizada');
    }

    public function cambiarEstado(Request $request, Garantia $garantia)
    {
        $garantia->update(['estado' => $request->estado]);
        return back()->with('success', 'Estado actualizado');
    }

    public function destroy(Garantia $garantia)
    {
        $garantia->delete();
        return back()->with('success', 'Garantía eliminada');
    }
}
