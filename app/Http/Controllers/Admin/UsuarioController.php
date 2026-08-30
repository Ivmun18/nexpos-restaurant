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
        $esNotaria = auth()->user()->empresa->industry_type === 'notaria';

        $request->validate([
            'name'     => 'required|max:100',
            'username' => ($esNotaria ? 'required' : 'nullable') . '|max:100|unique:users,username',
            'email'    => $esNotaria ? 'nullable' : 'nullable|email|unique:users,email',
            'password' => 'required|min:6',
            'rol'      => 'required|in:admin,cajero,mozo,cocinero,vendedor,notario,secretaria,asistente,prescripciones,legalizaciones,notificaciones,mixto',
        ]);

        if (!$esNotaria && !$request->email && !$request->username) {
            return back()->withErrors([
                'email' => 'Debes ingresar al menos un correo electrónico o un nombre de usuario.',
            ])->withInput();
        }

        User::create([
            'empresa_id' => auth()->user()->empresa_id,
            'name'       => $request->name,
            'username'   => $request->username,
            'email'      => $esNotaria ? null : $request->email,
            'password'   => Hash::make($request->password),
            'rol'        => $request->rol,
            'activo'     => true,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $usuario)
    {
        if ($usuario->empresa_id !== auth()->user()->empresa_id) {
            abort(403);
        }

        $esNotaria = auth()->user()->empresa->industry_type === 'notaria';

        $request->validate([
            'name'     => 'required|max:100',
            'username' => ($esNotaria ? 'required' : 'nullable') . '|max:100|unique:users,username,' . $usuario->id,
            'email'    => $esNotaria ? 'nullable' : 'nullable|email|unique:users,email,' . $usuario->id,
            'rol'      => 'required|in:admin,cajero,mozo,cocinero,vendedor,notario,secretaria,asistente,prescripciones,legalizaciones,notificaciones,mixto',
        ]);

        if (!$esNotaria && !$request->email && !$request->username) {
            return back()->withErrors([
                'email' => 'Debes ingresar al menos un correo electrónico o un nombre de usuario.',
            ])->withInput();
        }

        $datos = [
            'name'     => $request->name,
            'username' => $request->username,
            'rol'      => $request->rol,
        ];

        // El campo email no existe en el formulario de notaria: se preserva
        // el valor actual del usuario en vez de sobrescribirlo con null.
        if (!$esNotaria) {
            $datos['email'] = $request->email;
        }

        if ($request->password) {
            $request->validate(['password' => 'min:6']);
            $datos['password'] = Hash::make($request->password);
        }

        $usuario->update($datos);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleActivo(User $usuario)
    {
        if ($usuario->empresa_id !== auth()->user()->empresa_id) {
            abort(403);
        }

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propio usuario.');
        }

        $usuario->update(['activo' => !$usuario->activo]);

        $msg = $usuario->activo ? 'Usuario activado.' : 'Usuario desactivado.';
        return redirect()->route('usuarios.index')->with('success', $msg);
    }

    public function destroy(User $usuario)
    {
        if ($usuario->empresa_id !== auth()->user()->empresa_id) {
            abort(403);
        }

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }
        $usuario->delete();
        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
    }
}