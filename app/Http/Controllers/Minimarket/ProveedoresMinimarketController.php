<?php

namespace App\Http\Controllers\Minimarket;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProveedoresMinimarketController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('razon_social')->get();
        return Inertia::render('Minimarket/Proveedores', compact('proveedores'));
    }

    public function store(Request $request)
    {
        $request->validate(['razon_social' => 'required', 'numero_documento' => 'required']);
        Proveedor::create(array_merge($request->all(), ['empresa_id' => auth()->user()->empresa_id, 'activo' => true]));
        return back()->with('success', 'Proveedor creado');
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        abort_if($proveedor->empresa_id !== auth()->user()->empresa_id, 403);

        $request->validate(['razon_social' => 'required', 'numero_documento' => 'required']);

        // Mismo problema que en ClientesMinimarketController: empresa_id es
        // fillable, así que $request->all() dejaba reasignar el proveedor
        // de otra empresa a la propia.
        $proveedor->update($request->only([
            'tipo_documento', 'numero_documento', 'razon_social', 'nombre_comercial',
            'direccion', 'distrito', 'telefono', 'email', 'contacto_nombre',
            'dias_credito', 'agente_retencion',
        ]));

        return back()->with('success', 'Proveedor actualizado');
    }

    public function destroy(Proveedor $proveedor)
    {
        abort_if($proveedor->empresa_id !== auth()->user()->empresa_id, 403);

        $proveedor->delete();
        return back()->with('success', 'Proveedor eliminado');
    }
}
