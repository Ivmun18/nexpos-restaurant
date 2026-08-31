<?php

namespace App\Http\Controllers\Minimarket;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientesMinimarketController extends Controller
{
    public function index()
    {
        $clientes = Cliente::where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('razon_social')->get()
            ->map(function($c) {
                $c->numero_documento = (string) $c->numero_documento;
                return $c;
            });
        return Inertia::render('Minimarket/Clientes', compact('clientes'));
    }

    public function store(Request $request)
    {
        $request->validate(['razon_social' => 'required', 'numero_documento' => 'required']);
        Cliente::create(array_merge($request->all(), ['empresa_id' => auth()->user()->empresa_id, 'activo' => true]));
        return back()->with('success', 'Cliente creado');
    }

    public function update(Request $request, Cliente $cliente)
    {
        abort_if($cliente->empresa_id !== auth()->user()->empresa_id, 403);

        $request->validate(['razon_social' => 'required', 'numero_documento' => 'required']);

        // $request->all() antes se pasaba directo a update(): como
        // empresa_id es fillable en el modelo, cualquiera podía reasignar
        // el cliente de otra empresa a la propia con solo mandar ese campo
        // en el POST. Se restringe a los campos reales del formulario.
        $cliente->update($request->only([
            'tipo_documento', 'numero_documento', 'razon_social',
            'nombre_comercial', 'celular', 'email', 'direccion',
        ]));

        return back()->with('success', 'Cliente actualizado');
    }

    public function destroy(Cliente $cliente)
    {
        abort_if($cliente->empresa_id !== auth()->user()->empresa_id, 403);

        $cliente->delete();
        return back()->with('success', 'Cliente eliminado');
    }
}
