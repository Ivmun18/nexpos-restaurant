<?php

namespace App\Http\Controllers\Mesa;

use App\Helpers\EmpresaHelper;
use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MesaController extends Controller
{
    public function index()
    {
        // Verificar caja abierta
        $empresaId = auth()->user()->empresa_id;
        $caja = \App\Models\Caja::where('empresa_id', $empresaId)->where('activo', true)->first();
        if (!$caja) {
            $caja = \App\Models\Caja::create(['empresa_id' => $empresaId, 'codigo' => 'CAJA01', 'nombre' => 'Caja Principal', 'activo' => true]);
        }
        $sesionAbierta = \App\Models\SesionCaja::where('caja_id', $caja->id)
            ->where('estado', 'abierta')->first();
        if (!$sesionAbierta) {
            return redirect()->route('caja.index')->with('warning', '⚠️ Debes abrir la caja antes de atender mesas.');
        }

        $mesas = Mesa::where('empresa_id', EmpresaHelper::id())
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        $resumen = [
            'total'     => $mesas->count(),
            'libres'    => $mesas->where('estado', 'libre')->count(),
            'ocupadas'  => $mesas->where('estado', 'ocupada')->count(),
            'reservadas'=> $mesas->where('estado', 'reservada')->count(),
        ];

        return Inertia::render('Mesas/Index', [
            'mesas'   => $mesas,
            'resumen' => $resumen,
        ]);
    }

    public function cambiarEstado(Request $request, Mesa $mesa)
    {
        $request->validate([
            'estado' => 'required|in:libre,ocupada,reservada,bloqueada',
        ]);

        $mesa->update(['estado' => $request->estado]);

        return back()->with('success', 'Mesa actualizada.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero'   => 'required|max:10',
            'nombre'   => 'nullable|max:50',
            'capacidad'=> 'required|integer|min:1|max:20',
            'zona'     => 'required|in:salon,terraza,barra,privado,delivery',
        ]);

        $ultimo = Mesa::where('empresa_id', EmpresaHelper::id())->max('orden') ?? 0;

        Mesa::create([
            'empresa_id' => EmpresaHelper::id(),
            'numero'     => $request->numero,
            'nombre'     => $request->nombre ?? 'Mesa ' . $request->numero,
            'capacidad'  => $request->capacidad,
            'zona'       => $request->zona,
            'estado'     => 'libre',
            'orden'      => $ultimo + 1,
        ]);

        return back()->with('success', 'Mesa creada correctamente.');
    }

    public function update(Request $request, Mesa $mesa)
    {
        $request->validate([
            'nombre'   => 'required|max:50',
            'capacidad'=> 'required|integer|min:1|max:20',
            'zona'     => 'required|in:salon,terraza,barra,privado,delivery',
        ]);

        $mesa->update([
            'nombre'   => $request->nombre,
            'capacidad'=> $request->capacidad,
            'zona'     => $request->zona,
            'activo'   => $request->activo ?? true,
        ]);

        return back()->with('success', 'Mesa actualizada correctamente.');
    }

    public function destroy(Mesa $mesa)
    {
        $mesa->delete();
        return back()->with('success', 'Mesa eliminada.');
    }
}
