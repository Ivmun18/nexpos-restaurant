<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class EmpresaHelper
{
    public static function id(): int
    {
        return Auth::user()?->empresa_id ?? 1;
    }

    public static function empresa()
    {
        return app()->bound('empresa_actual')
            ? app('empresa_actual')
            : \App\Models\Empresa::find(self::id());
    }

    public static function esAdmin(): bool
    {
        return Auth::user()?->rol === 'admin';
    }

    public static function rol(): string
    {
        return Auth::user()?->rol ?? 'vendedor';
    }
}