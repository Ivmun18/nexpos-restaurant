<?php

namespace App\Http\Controllers;

use App\Helpers\EmpresaHelper;
use App\Models\ProductoVarianteGrupo;
use Illuminate\Http\Request;

class ProductoVarianteController extends Controller
{
    /**
     * Grupos y opciones de variantes de un producto (ej: combo con
     * acompañamiento y bebida a elegir). Usado por el modal del POS.
     */
    public function api(Request $request)
    {
        $productoId = $request->input('producto_id');
        $empresaId  = EmpresaHelper::id();

        if (!$productoId) {
            return response()->json([]);
        }

        $grupos = ProductoVarianteGrupo::where('producto_id', $productoId)
            ->whereHas('producto', fn ($q) => $q->where('empresa_id', $empresaId))
            ->with(['opciones:id,grupo_id,nombre'])
            ->orderBy('orden')
            ->get(['id', 'producto_id', 'nombre', 'requerido', 'orden']);

        return response()->json($grupos);
    }
}
