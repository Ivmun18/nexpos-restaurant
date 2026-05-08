<?php

namespace App\Http\Controllers\Ferreteria;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProveedoresFerretoriaController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('razon_social')->get();
        return Inertia::render('Ferreteria/Proveedores', compact('proveedores'));
    }

    public function store(Request $request)
    {
        $request->validate(['razon_social' => 'required', 'numero_documento' => 'required']);
        Proveedor::create(array_merge($request->all(), ['empresa_id' => auth()->user()->empresa_id, 'activo' => true]));
        return back()->with('success', 'Proveedor creado');
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $proveedor->update($request->all());
        return back()->with('success', 'Proveedor actualizado');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();
        return back()->with('success', 'Proveedor eliminado');
    }
}
