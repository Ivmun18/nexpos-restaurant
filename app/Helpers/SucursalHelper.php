<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Analoga a EmpresaHelper pero para sucursal: admin (sucursal_id NULL) ve
 * todas las sucursales de su empresa; mozo/cajero/cocina quedan acotados a
 * la suya.
 */
class SucursalHelper
{
    public static function id(): ?int
    {
        return Auth::user()?->sucursal_id;
    }

    public static function verTodas(): bool
    {
        return Auth::user()?->esAdmin() ?? true;
    }

    public static function aplicarFiltro(Builder $query, string $columna = 'sucursal_id'): Builder
    {
        if (! self::verTodas() && self::id()) {
            $query->where($columna, self::id());
        }

        return $query;
    }
}
