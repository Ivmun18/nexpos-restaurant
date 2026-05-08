<?php

namespace App\Http\Controllers\Ferreteria;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\CategoriaMinimarket as Categoria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosFerretoriaController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;
        $productos  = Producto::where('empresa_id', $empresa_id)->where('activo', true)->with('categoria')->get();
        $categorias = Categoria::where('empresa_id', $empresa_id)->orderBy('nombre')->get();
        return Inertia::render('Ferreteria/Pos', compact('productos', 'categorias'));
    }

    public function store(Request $request)
    {
        foreach ($request->items as $item) {
            $producto = Producto::find($item['id']);
            if ($producto) {
                $producto->decrement('stock_actual', $item['cantidad']);
            }
        }
        return back()->with('success', 'Venta registrada correctamente');
    }
}
