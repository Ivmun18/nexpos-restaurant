<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function show()
    {
        return Inertia::render('Auth/Login');
    }

 public function store(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    // Verificar si el email existe
    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user) {
        return back()->withErrors([
            'email' => 'No encontramos una cuenta con ese correo electrónico.',
        ]);
    }

    // Verificar si está activo
    if (!$user->activo) {
        return back()->withErrors([
            'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
        ]);
    }

    // Verificar contraseña
    if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
        return back()->withErrors([
            'password' => 'La contraseña es incorrecta.',
        ]);
    }

    $request->session()->regenerate();
    return redirect()->intended('/dashboard');
}

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}

