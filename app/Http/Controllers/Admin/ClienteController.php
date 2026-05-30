<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Helpers\EmpresaHelper;
use Inertia\Inertia;

class ClienteController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $buscar = $request->get('buscar', '');

        $query = Cliente::where('empresa_id', EmpresaHelper::id())->orderBy('razon_social');

        if ($buscar) {
            $query->where(function($q) use ($buscar) {
                $q->where('razon_social', 'like', "%{$buscar}%")
                  ->orWhere('numero_documento', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%");
            });
        }

        $clientes = $query->paginate(20)->withQueryString();

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'filtros'  => ['buscar' => $buscar],
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

        Cliente::create([
            'empresa_id'       => EmpresaHelper::id(),
            'tipo_documento'   => $request->tipo_documento,
            'numero_documento' => $request->numero_documento,
            'razon_social'     => strtoupper($request->razon_social),
            'email'            => $request->email,
            'telefono'         => $request->telefono,
            'direccion'        => $request->direccion,
        ]);

        return back()->with('success', 'Cliente registrado correctamente.');
    }

    public function storeJson(Request $request)
    {
        $request->validate([
            'tipo_documento'   => 'required|max:1',
            'numero_documento' => 'required|max:15',
            'razon_social'     => 'required|max:200',
            'email'            => 'nullable|email|max:150',
            'telefono'         => 'nullable|max:20',
            'direccion'        => 'nullable|max:300',
        ]);

        try {
            $cliente = \App\Models\Cliente::create([
                'empresa_id'       => auth()->user()->empresa_id,
                'tipo_documento'   => $request->tipo_documento,
                'numero_documento' => $request->numero_documento,
                'razon_social'     => strtoupper($request->razon_social),
                'email'            => $request->email,
                'telefono'         => $request->telefono,
                'direccion'        => $request->direccion,
            ]);
            return response()->json(['success' => true, 'id' => $cliente->id, 'razon_social' => $cliente->razon_social]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            $existente = \App\Models\Cliente::where('empresa_id', auth()->user()->empresa_id)
                ->where('numero_documento', $request->numero_documento)->first();
            return response()->json(['success' => true, 'id' => $existente->id, 'razon_social' => $existente->razon_social, 'mensaje' => 'Cliente ya registrado']);
        }
    }

    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'razon_social' => 'required|max:200',
            'email'        => 'nullable|email|max:150',
            'telefono'     => 'nullable|max:20',
            'direccion'    => 'nullable|max:300',
        ]);

        $cliente->update([
            'tipo_documento'   => $request->tipo_documento,
            'numero_documento' => $request->numero_documento,
            'razon_social'     => strtoupper($request->razon_social),
            'email'            => $request->email,
            'telefono'         => $request->telefono,
            'direccion'        => $request->direccion,
        ]);

        return back()->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return back()->with('success', 'Cliente eliminado correctamente.');
    }
}
