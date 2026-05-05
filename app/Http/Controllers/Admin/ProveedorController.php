<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use App\Helpers\EmpresaHelper;
use Inertia\Inertia;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::orderBy('razon_social')->paginate(20);

        return Inertia::render('Proveedores/Index', [
            'proveedores' => $proveedores,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_documento'   => 'required|max:1',
            'numero_documento' => 'required|max:15',
            'razon_social'     => 'required|max:200',
            'email'            => 'nullable|email|max:150',
            'telefono'         => 'nullable|max:20',
            'direccion'        => 'nullable|max:300',
        ]);

        Proveedor::create([
            'empresa_id'       => EmpresaHelper::id(),
            'tipo_documento'   => $request->tipo_documento,
            'numero_documento' => $request->numero_documento,
            'razon_social'     => strtoupper($request->razon_social),
            'nombre_comercial' => $request->nombre_comercial,
            'email'            => $request->email,
            'telefono'         => $request->telefono,
            'direccion'        => $request->direccion,
            'contacto_nombre'  => $request->contacto_nombre,
            'dias_credito'     => $request->dias_credito ?? 0,
            'agente_retencion' => $request->agente_retencion ?? false,
        ]);

        return back()->with('success', 'Proveedor registrado correctamente.');
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $request->validate([
            'razon_social' => 'required|max:200',
            'email'        => 'nullable|email|max:150',
        ]);

        $proveedor->update([
            'razon_social'    => strtoupper($request->razon_social),
            'nombre_comercial'=> $request->nombre_comercial,
            'email'           => $request->email,
            'telefono'        => $request->telefono,
            'direccion'       => $request->direccion,
            'contacto_nombre' => $request->contacto_nombre,
            'dias_credito'    => $request->dias_credito ?? 0,
            'agente_retencion'=> $request->agente_retencion ?? false,
            'activo'          => $request->activo ?? true,
        ]);

        return back()->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();
        return back()->with('success', 'Proveedor eliminado correctamente.');
    }
}
