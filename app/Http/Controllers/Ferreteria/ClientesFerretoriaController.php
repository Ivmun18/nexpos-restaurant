<?php

namespace App\Http\Controllers\Ferreteria;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientesFerretoriaController extends Controller
{
    public function index()
    {
        $clientes = Cliente::where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('razon_social')->get();
        return Inertia::render('Ferreteria/Clientes', compact('clientes'));
    }

    public function store(Request $request)
    {
        $request->validate(['razon_social' => 'required', 'numero_documento' => 'required']);
        Cliente::create(array_merge($request->all(), ['empresa_id' => auth()->user()->empresa_id, 'activo' => true]));
        return back()->with('success', 'Cliente creado');
    }

    public function update(Request $request, Cliente $cliente)
    {
        $cliente->update($request->all());
        return back()->with('success', 'Cliente actualizado');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return back()->with('success', 'Cliente eliminado');
    }
}
