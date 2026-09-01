<?php

namespace App\Http\Controllers\Mesa;

use App\Helpers\EmpresaHelper;
use App\Helpers\SucursalHelper;
use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MesaController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Verificar caja abierta
        $empresaId  = auth()->user()->empresa_id;
        $sucursalId = SucursalHelper::id();
        $caja = \App\Models\Caja::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->when($sucursalId !== null, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->orderBy('id')
            ->first();
        if (!$caja) {
            $caja = \App\Models\Caja::create(['empresa_id' => $empresaId, 'sucursal_id' => $sucursalId, 'codigo' => 'CAJA01', 'nombre' => 'Caja Principal', 'activo' => true]);
        }
        $sesionAbierta = \App\Models\SesionCaja::where('caja_id', $caja->id)
            ->where('estado', 'abierta')->first();
        if (!$sesionAbierta) {
            return redirect()->route('caja.index')->with('warning', '⚠️ Debes abrir la caja antes de atender mesas.');
        }

        $mesas = SucursalHelper::aplicarFiltro(
            Mesa::with('sucursal:id,nombre')
                ->where('empresa_id', EmpresaHelper::id())
                ->where('activo', true)
        )
            ->orderBy('orden')
            ->get();

        $resumen = [
            'total'     => $mesas->count(),
            'libres'    => $mesas->where('estado', 'libre')->count(),
            'ocupadas'  => $mesas->where('estado', 'ocupada')->count(),
            'reservadas'=> $mesas->where('estado', 'reservada')->count(),
        ];

        $sucursales = \App\Models\Sucursal::where('empresa_id', EmpresaHelper::id())
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return Inertia::render('Mesas/Index', [
            'mesas'      => $mesas,
            'resumen'    => $resumen,
            'sucursales' => $sucursales,
        ]);
    }

    public function cambiarEstado(Request $request, Mesa $mesa)
    {
        if ($mesa->empresa_id !== EmpresaHelper::id()) {
            abort(403);
        }

        $request->validate([
            'estado' => 'required|in:libre,ocupada,reservada,bloqueada',
        ]);

        $mesa->update(['estado' => $request->estado]);

        return back()->with('success', 'Mesa actualizada.');
    }

    public function store(Request $request)
    {
        $empresaId = EmpresaHelper::id();

        // Solo un admin puede elegir explicitamente la sucursal; el resto
        // de roles siempre queda en la suya propia (evita que un mozo de
        // Local 2 spoofee sucursal_id y cree mesas en Local 1). Se resuelve
        // antes de validar para poder chequear unicidad de numero contra la
        // sucursal real con la que se va a guardar la mesa.
        $sucursalId = SucursalHelper::id();
        if (EmpresaHelper::esAdmin() && $request->filled('sucursal_id')) {
            $sucursalId = $request->sucursal_id;
        }

        $request->validate([
            'numero' => [
                'required',
                'max:10',
                Rule::unique('mesas')->where(function ($query) use ($empresaId, $sucursalId) {
                    $query->where('empresa_id', $empresaId);
                    $sucursalId === null ? $query->whereNull('sucursal_id') : $query->where('sucursal_id', $sucursalId);
                }),
            ],
            'nombre'      => 'nullable|max:50',
            'capacidad'   => 'required|integer|min:1|max:20',
            'zona'        => 'required|in:salon,terraza,barra,privado,delivery',
            'sucursal_id' => 'nullable|exists:sucursales,id',
        ], [
            'numero.unique' => 'Ya existe una mesa con el número ' . $request->numero . ' en esta sucursal. Elegí otro número.',
        ]);

        $ultimo = Mesa::where('empresa_id', EmpresaHelper::id())->max('orden') ?? 0;

        Mesa::create([
            'empresa_id'  => EmpresaHelper::id(),
            'sucursal_id' => $sucursalId,
            'numero'      => $request->numero,
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
        if ($mesa->empresa_id !== EmpresaHelper::id()) {
            abort(403);
        }

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
        if ($mesa->empresa_id !== EmpresaHelper::id()) {
            abort(403);
        }

        $mesa->delete();
        return back()->with('success', 'Mesa eliminada.');
    }

    // Transferir los pedidos abiertos de una mesa a otra
    public function transferir(Request $request, Mesa $mesa)
    {
        if ($mesa->empresa_id !== EmpresaHelper::id()) {
            abort(403);
        }

        $request->validate([
            'destino_id' => 'required|exists:mesas,id|different:id',
        ]);

        // exists:mesas,id solo confirma que el id existe en la tabla (de
        // cualquier empresa/sucursal) — sin este chequeo se podían mover
        // pedidos de una mesa hacia una mesa de OTRA empresa, o de otra
        // sucursal dentro de la misma empresa (mismo problema de fondo que
        // el bug de caja entre Local 1 y Local 2).
        $destino = Mesa::where('id', $request->destino_id)
            ->where('empresa_id', $mesa->empresa_id)
            ->where('sucursal_id', $mesa->sucursal_id)
            ->first();

        if (! $destino) {
            return back()->with('error', 'La mesa destino no existe en esta sucursal.');
        }

        if ($destino->estado !== 'libre') {
            return back()->with('error', "La mesa {$destino->numero} no esta libre.");
        }

        // Mover pedidos abiertos a la mesa destino
        \App\Models\Pedido::where('mesa_id', $mesa->id)
            ->whereIn('estado', ['abierto', 'enviado', 'listo'])
            ->update(['mesa_id' => $destino->id]);

        // Origen libre, destino ocupada
        $mesa->update(['estado' => 'libre']);
        $destino->update(['estado' => 'ocupada']);

        return back()->with('success', "Pedidos transferidos de Mesa {$mesa->numero} a Mesa {$destino->numero}.");
    }

    // Unir una mesa secundaria a una principal (cuenta combinada)
    public function unir(Request $request, Mesa $mesa)
    {
        if ($mesa->empresa_id !== EmpresaHelper::id()) {
            abort(403);
        }

        $request->validate([
            'secundaria_id' => 'required|exists:mesas,id|different:id',
        ]);

        $secundaria = Mesa::where('id', $request->secundaria_id)
            ->where('empresa_id', $mesa->empresa_id)
            ->where('sucursal_id', $mesa->sucursal_id)
            ->first();

        if (! $secundaria) {
            return back()->with('error', 'La mesa secundaria no existe en esta sucursal.');
        }

        // La principal es $mesa; la secundaria se enlaza a ella
        $secundaria->update([
            'mesa_principal_id' => $mesa->id,
            'estado'            => 'ocupada',
        ]);
        $mesa->update(['estado' => 'ocupada']);

        return back()->with('success', "Mesa {$secundaria->numero} unida a Mesa {$mesa->numero}.");
    }

    // Separar (deshacer union)
    public function separar(Mesa $mesa)
    {
        if ($mesa->empresa_id !== EmpresaHelper::id()) {
            abort(403);
        }

        // Si es secundaria, se desenlaza
        $mesa->update(['mesa_principal_id' => null]);

        // Tambien desenlazar cualquier mesa que apunte a esta como principal
        Mesa::where('mesa_principal_id', $mesa->id)->update(['mesa_principal_id' => null]);

        return back()->with('success', "Mesa {$mesa->numero} separada.");
    }

}
