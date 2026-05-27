<?php

namespace App\Http\Controllers\Notaria;

use App\Http\Controllers\Controller;
use App\Models\ActoNotarial;
use App\Models\ActoRequisito;
use Illuminate\Http\Request;

class ActoRequisitoController extends Controller
{
    /**
     * Agregar requisito al acto
     */
    public function store(Request $request, ActoNotarial $acto)
    {
        $validated = $request->validate([
            'documento' => 'required|string|max:255',
            'observacion' => 'nullable|string',
        ]);

        ActoRequisito::create([
            'acto_id' => $acto->id,
            'documento' => $validated['documento'],
            'entregado' => false,
            'observacion' => $validated['observacion'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Requisito agregado correctamente');
    }

    /**
     * Marcar/desmarcar requisito como entregado
     */
    public function toggle(ActoRequisito $requisito)
    {
        $requisito->update([
            'entregado' => !$requisito->entregado,
        ]);

        return back()->with('success', 'Requisito actualizado');
    }

    /**
     * Eliminar requisito
     */
    public function destroy(ActoRequisito $requisito)
    {
        $requisito->delete();

        return back()->with('success', 'Requisito eliminado');
    }
}
