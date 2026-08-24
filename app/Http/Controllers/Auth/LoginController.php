<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AuditoriaLog;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function show(Request $request)
    {
        $host = $request->getHost();
        if ($host === 'minimarket.nexposolution.com') {
            return Inertia::render('Auth/LoginMinimarket');
        }

        $hostEmpresaMap = [
            'pencuentro.nexposolution.com' => 23,
        ];

        $empresa = null;
        if (isset($hostEmpresaMap[$host])) {
            $empresa = \DB::table('empresas')
                ->select('id', 'razon_social', 'nombre_comercial', 'logo')
                ->where('id', $hostEmpresaMap[$host])
                ->first();
        }

        return Inertia::render('Auth/Login', [
            'empresa' => $empresa,
        ]);
    }

 public function store(Request $request)
{
    $request->validate([
        'email'    => 'required|string',
        'password' => 'required',
    ]);

    // Buscar por email, nombre de usuario (username) o nombre completo
    $user = \App\Models\User::where('email', $request->email)
        ->orWhere('username', $request->email)
        ->orWhere('name', $request->email)
        ->first();

    if (!$user) {
        return back()->withErrors([
            'email' => 'No encontramos una cuenta con ese correo o usuario.',
        ]);
    }

    // Verificar si está activo
    if (!$user->activo) {
        return back()->withErrors([
            'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
        ]);
    }

    // Verificar contraseña contra el hash del usuario ya identificado.
    // No se usa Auth::attempt(['email' => ...]) porque email puede ser NULL
    // (usuarios de notaria sin correo): varios usuarios comparten NULL y
    // Auth::attempt buscaria por ese criterio, pudiendo autenticar contra
    // el usuario equivocado. Como ya tenemos $user resuelto por email/
    // username/name, verificamos el hash directamente y logueamos manual.
    if (!Hash::check($request->password, $user->password)) {
        AuditoriaLog::registrar(
            'auth',
            'login_fallido',
            'usuario',
            null,
            $request->email,
            null,
            ['email' => $request->email],
            'Intento de login fallido (contraseña incorrecta)',
            'warning',
            $user->empresa_id
        );

        return back()->withErrors([
            'password' => 'La contraseña es incorrecta.',
        ]);
    }

    Auth::login($user);
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
    
    $empresa = \DB::table('empresas')->where('id', auth()->user()->empresa_id)->first();
    $industryRedirects = [
        'gimnasio'    => '/gimnasio/dashboard',
        'hotel'       => '/hotel/dashboard',
        'notaria'     => '/notaria/actos',
        'minimarket'  => '/minimarket/pos',
        'odontologia' => '/odontologia/dashboard',
        'optica'      => '/optica/dashboard',
    ];
    if ($empresa && isset($industryRedirects[$empresa->industry_type])) {
        return redirect()->intended($industryRedirects[$empresa->industry_type]);
    }

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

