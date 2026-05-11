<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Usuarios', [
            'usuarios'  => $usuarios,
            'industria' => auth()->user()->empresa->industry_type ?? 'restaurante',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'rol'      => 'required|in:admin,cajero,mozo,cocinero,vendedor',
        ]);

        User::create([
            'empresa_id' => auth()->user()->empresa_id,
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'rol'        => $request->rol,
            'activo'     => true,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name'  => 'required|max:100',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'rol'   => 'required|in:admin,cajero,mozo,cocinero,vendedor',
        ]);

        $datos = [
            'name'  => $request->name,
            'email' => $request->email,
            'rol'   => $request->rol,
        ];

        if ($request->password) {
            $request->validate(['password' => 'min:6']);
            $datos['password'] = Hash::make($request->password);
        }

        $usuario->update($datos);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleActivo(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propio usuario.');
        }

        $usuario->update(['activo' => !$usuario->activo]);

        $msg = $usuario->activo ? 'Usuario activado.' : 'Usuario desactivado.';
        return redirect()->route('usuarios.index')->with('success', $msg);
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }
        $usuario->delete();
        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
    }
}