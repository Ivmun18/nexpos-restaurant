<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('razon_social')
            ->get();

        return Inertia::render('Proveedores/Index', compact('proveedores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'razon_social'     => 'required|string|max:200',
            'tipo_documento'   => 'required|string',
            'numero_documento' => 'required|string|max:20',
            'email'            => 'nullable|email|max:150',
            'telefono'         => 'nullable|string|max:20',
        ]);

        Proveedor::create([
            'empresa_id'       => auth()->user()->empresa_id,
            'tipo_documento'   => $request->tipo_documento,
            'numero_documento' => $request->numero_documento,
            'razon_social'     => strtoupper($request->razon_social),
            'nombre_comercial' => $request->nombre_comercial,
            'direccion'        => $request->direccion,
            'distrito'         => $request->distrito,
            'provincia'        => $request->provincia,
            'departamento'     => $request->departamento,
            'telefono'         => $request->telefono,
            'email'            => $request->email,
            'contacto_nombre'  => $request->contacto_nombre,
            'dias_credito'     => $request->dias_credito ?? 0,
            'agente_retencion' => $request->agente_retencion ?? false,
            'activo'           => true,
        ]);

        return back()->with('success', 'Proveedor creado correctamente.');
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $request->validate([
            'razon_social'     => 'required|string|max:200',
            'tipo_documento'   => 'required|string',
            'numero_documento' => 'required|string|max:20',
            'email'            => 'nullable|email|max:150',
            'telefono'         => 'nullable|string|max:20',
        ]);

        $proveedor->update([
            'tipo_documento'   => $request->tipo_documento,
            'numero_documento' => $request->numero_documento,
            'razon_social'     => strtoupper($request->razon_social),
            'nombre_comercial' => $request->nombre_comercial,
            'direccion'        => $request->direccion,
            'distrito'         => $request->distrito,
            'provincia'        => $request->provincia,
            'departamento'     => $request->departamento,
            'telefono'         => $request->telefono,
            'email'            => $request->email,
            'contacto_nombre'  => $request->contacto_nombre,
            'dias_credito'     => $request->dias_credito ?? 0,
            'agente_retencion' => $request->agente_retencion ?? false,
            'activo'           => $request->activo ?? true,
        ]);

        return back()->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->update(['activo' => false]);
        return back()->with('success', 'Proveedor desactivado correctamente.');
    }
}
