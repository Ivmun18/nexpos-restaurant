<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\EmpresaHelper;
use App\Http\Controllers\Controller;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SucursalController extends Controller
{
    /**
     * Panel consolidado: mesas ocupadas, pedidos pendientes y ventas del
     * dia por cada sucursal de la empresa.
     */
    public function dashboard()
    {
        $empresaId = EmpresaHelper::id();

        $sucursales = Sucursal::where('empresa_id', $empresaId)
            ->orderBy('nombre')
            ->get()
            ->map(function (Sucursal $sucursal) {
                return [
                    'id'                => $sucursal->id,
                    'nombre'            => $sucursal->nombre,
                    'direccion'         => $sucursal->direccion,
                    'telefono'          => $sucursal->telefono,
                    'activo'            => $sucursal->activo,
                    'mesas_ocupadas'    => Mesa::where('sucursal_id', $sucursal->id)
                        ->where('estado', 'ocupada')
                        ->count(),
                    'pedidos_pendientes'=> Pedido::where('sucursal_id', $sucursal->id)
                        ->where('estado', '!=', 'cerrado')
                        ->count(),
                    'ventas_dia'        => (float) Pedido::where('sucursal_id', $sucursal->id)
                        ->where('estado', 'cerrado')
                        ->whereDate('created_at', today())
                        ->sum('total'),
                ];
            });

        return Inertia::render('Admin/Sucursales', [
            'sucursales' => $sucursales,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'    => 'required|max:100',
            'direccion' => 'nullable|max:300',
            'telefono'  => 'nullable|max:20',
        ]);

        Sucursal::create([
            'empresa_id' => EmpresaHelper::id(),
            'nombre'     => $request->nombre,
            'direccion'  => $request->direccion,
            'telefono'   => $request->telefono,
            'activo'     => true,
        ]);

        return back()->with('success', 'Sucursal creada correctamente.');
    }

    public function update(Request $request, Sucursal $sucursal)
    {
        $request->validate([
            'nombre'    => 'required|max:100',
            'direccion' => 'nullable|max:300',
            'telefono'  => 'nullable|max:20',
            'activo'    => 'boolean',
        ]);

        $sucursal->update($request->only(['nombre', 'direccion', 'telefono', 'activo']));

        return back()->with('success', 'Sucursal actualizada correctamente.');
    }

    public function destroy(Sucursal $sucursal)
    {
        $tieneDependientes = Mesa::where('sucursal_id', $sucursal->id)->exists()
            || Pedido::where('sucursal_id', $sucursal->id)->exists()
            || \App\Models\User::where('sucursal_id', $sucursal->id)->exists();

        if ($tieneDependientes) {
            return back()->with('error', 'No puedes eliminar una sucursal con mesas, pedidos o usuarios asignados. Desactivala en su lugar.');
        }

        $sucursal->delete();

        return back()->with('success', 'Sucursal eliminada.');
    }
}
