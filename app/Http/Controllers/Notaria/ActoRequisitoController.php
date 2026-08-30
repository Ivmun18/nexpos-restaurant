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
        // El route-model-binding de {acto} no filtra por empresa: sin este
        // chequeo, cualquier usuario autenticado de OTRA notaría podía
        // adivinar/enumerar un id de expediente ajeno y agregarle
        // requisitos.
        if ($acto->empresa_id !== auth()->user()->empresa_id) {
            abort(403);
        }

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
        if ($requisito->acto->empresa_id !== auth()->user()->empresa_id) {
            abort(403);
        }

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
        if ($requisito->acto->empresa_id !== auth()->user()->empresa_id) {
            abort(403);
        }

        $requisito->delete();

        return back()->with('success', 'Requisito eliminado');
    }
}
