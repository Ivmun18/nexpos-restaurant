<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\InsumoMovimiento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InsumoController extends Controller
{
    public function index(Request $request)
    {
        $buscar    = $request->get('buscar', '');
        $categoria = $request->get('categoria', '');
        $alerta    = $request->get('alerta', '');

        $query = Insumo::where('empresa_id', 1)->where('activo', true);

        if ($buscar)    $query->where('nombre', 'like', "%{$buscar}%");
        if ($categoria) $query->where('categoria', $categoria);
        if ($alerta)    $query->whereColumn('stock_actual', '<=', 'stock_minimo');

        $insumos = $query->orderBy('nombre')->get();

        $categorias = Insumo::where('empresa_id', 1)
            ->distinct()->pluck('categoria')->filter()->values();

        $alertas = Insumo::where('empresa_id', 1)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->count();

        return Inertia::render('Insumos/Index', [
            'insumos'    => $insumos,
            'categorias' => $categorias,
            'alertas'    => $alertas,
            'filtros'    => compact('buscar', 'categoria', 'alerta'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'        => 'required|string|max:150',
            'categoria'     => 'nullable|string|max:80',
            'unidad_medida' => 'required|string|max:20',
            'stock_minimo'  => 'required|numeric|min:0',
            'stock_actual'  => 'required|numeric|min:0',
        ]);

        $insumo = Insumo::create([
            'empresa_id'    => 1,
            'nombre'        => $request->nombre,
            'categoria'     => $request->categoria,
            'unidad_medida' => $request->unidad_medida,
            'stock_actual'  => $request->stock_actual,
            'stock_minimo'  => $request->stock_minimo,
            'precio_promedio'=> 0,
            'activo'        => true,
        ]);

        if ($request->stock_actual > 0) {
            InsumoMovimiento::create([
                'insumo_id'      => $insumo->id,
                'user_id'        => auth()->id(),
                'tipo'           => 'entrada',
                'cantidad'       => $request->stock_actual,
                'costo_unitario' => 0,
                'stock_anterior' => 0,
                'stock_nuevo'    => $request->stock_actual,
                'motivo'         => 'Stock inicial',
            ]);
        }

        return back()->with('success', 'Insumo registrado correctamente.');
    }

    public function update(Request $request, Insumo $insumo)
    {
        $request->validate([
            'nombre'        => 'required|string|max:150',
            'categoria'     => 'nullable|string|max:80',
            'unidad_medida' => 'required|string|max:20',
            'stock_minimo'  => 'required|numeric|min:0',
        ]);

        $insumo->update($request->only(['nombre', 'categoria', 'unidad_medida', 'stock_minimo', 'observaciones']));

        return back()->with('success', 'Insumo actualizado.');
    }

    public function movimiento(Request $request, Insumo $insumo)
    {
        $request->validate([
            'tipo'           => 'required|in:entrada,salida,ajuste',
            'cantidad'       => 'required|numeric|min:0.001',
            'motivo'         => 'required|string|max:200',
            'costo_unitario' => 'nullable|numeric|min:0',
        ]);

        $stockAnterior = $insumo->stock_actual;
        $cantidad      = $request->cantidad;
        $tipo          = $request->tipo;

        if ($tipo === 'entrada') {
            $stockNuevo = $stockAnterior + $cantidad;
        } elseif ($tipo === 'salida') {
            if ($cantidad > $stockAnterior) {
                return back()->with('error', 'Stock insuficiente.');
            }
            $stockNuevo = $stockAnterior - $cantidad;
        } else {
            $stockNuevo = $cantidad;
        }

        $insumo->update(['stock_actual' => $stockNuevo]);

        if ($tipo === 'entrada' && $request->costo_unitario > 0) {
            $totalAnterior  = $stockAnterior * $insumo->precio_promedio;
            $totalNuevo     = $cantidad * $request->costo_unitario;
            $precioPromedio = ($stockAnterior + $cantidad) > 0
                ? ($totalAnterior + $totalNuevo) / ($stockAnterior + $cantidad)
                : $request->costo_unitario;
            $insumo->update(['precio_promedio' => round($precioPromedio, 4)]);
        }

        InsumoMovimiento::create([
            'insumo_id'      => $insumo->id,
            'user_id'        => auth()->id(),
            'tipo'           => $tipo,
            'cantidad'       => $cantidad,
            'costo_unitario' => $request->costo_unitario ?? 0,
            'stock_anterior' => $stockAnterior,
            'stock_nuevo'    => $stockNuevo,
            'motivo'         => $request->motivo,
        ]);

        return back()->with('success', 'Movimiento registrado. Stock actualizado.');
    }

    public function destroy(Insumo $insumo)
    {
        $insumo->update(['activo' => false]);
        return back()->with('success', 'Insumo desactivado.');
    }
}
