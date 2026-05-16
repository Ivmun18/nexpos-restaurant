<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditoriaLog;
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
        AuditoriaLog::registrar(
            'auth',
            'login_fallido',
            'usuario',
            null,
            $request->email,
            null,
            ['email' => $request->email],
            'Intento de login fallido (contraseña incorrecta)',
            'warning'
        );
        
        return back()->withErrors([
            'password' => 'La contraseña es incorrecta.',
        ]);
    }

    $request->session()->regenerate();
    
    AuditoriaLog::registrar(
        'auth',
        'login',
        'usuario',
        auth()->id(),
        auth()->user()->name,
        null,
        ['email' => $request->email],
        'Inicio de sesión exitoso'
    );
    
    return redirect()->intended('/dashboard');
}

    public function destroy(Request $request)
    {
        // Capturar nombre ANTES de hacer logout
        $userId = auth()->id();
        $userName = auth()->user()?->name;
        
        if ($userId) {
            AuditoriaLog::registrar(
                'auth',
                'logout',
                'usuario',
                $userId,
                $userName,
                null,
                null,
                'Cierre de sesión'
            );
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}

