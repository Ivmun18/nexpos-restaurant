<?php

namespace App\Http\Controllers\Minimarket;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\TrasladoMercaderia;
use App\Models\TrasladoMercaderiaDetalle;
use App\Models\InventarioMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TrasladosMinimarketController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;

        $productos = Producto::where('empresa_id', $empresa_id)
            ->with(['presentaciones' => fn($q) => $q->where('activo', true)])
            ->orderBy('descripcion')
            ->get(['id', 'descripcion', 'codigo', 'codigo_barras', 'stock_actual']);

        $traslados = TrasladoMercaderia::where('empresa_id', $empresa_id)
            ->with(['detalle.producto', 'usuario'])
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Minimarket/Traslados', [
            'productos'  => $productos,
            'traslados'  => $traslados,
        ]);
    }

    public function store(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;

        $request->validate([
            'local_destino'   => 'required|string|max:150',
            'transportista'   => 'nullable|string|max:150',
            'placa_vehiculo'  => 'nullable|string|max:20',
            'observaciones'   => 'nullable|string|max:255',
            'items'           => 'required|array|min:1',
            'items.*.producto_id'     => 'required|exists:productos,id',
            'items.*.cantidad'        => 'required|numeric|min:0.01',
            'items.*.presentacion_id' => 'nullable|exists:producto_presentaciones,id',
        ]);

        DB::transaction(function () use ($request, $empresa_id) {
            $traslado = TrasladoMercaderia::create([
                'empresa_id'      => $empresa_id,
                'usuario_id'      => auth()->id(),
                'local_destino'   => $request->local_destino,
                'transportista'   => $request->transportista,
                'placa_vehiculo'  => $request->placa_vehiculo,
                'observaciones'   => $request->observaciones,
            ]);

            foreach ($request->items as $item) {
                $producto = Producto::where('id', $item['producto_id'])
                    ->where('empresa_id', $empresa_id)
                    ->lockForUpdate()
                    ->first();

                abort_if(!$producto, 403);

                $factor = 1;
                if (!empty($item['presentacion_id'])) {
                    $presentacion = \App\Models\ProductoPresentacion::where('id', $item['presentacion_id'])
                        ->where('producto_id', $producto->id)
                        ->first();
                    if ($presentacion) {
                        $factor = $presentacion->factor_conversion;
                    }
                }

                $stockAnterior = $producto->stock_actual;
                $cantidadReal = $item['cantidad'] * $factor;

                if ($stockAnterior < $cantidadReal) {
                    throw new \Exception("Stock insuficiente para {$producto->descripcion}. Disponible: {$stockAnterior}, solicitado: {$cantidadReal}");
                }

                $stockNuevo = $stockAnterior - $cantidadReal;
                $producto->update(['stock_actual' => $stockNuevo]);

                TrasladoMercaderiaDetalle::create([
                    'traslado_id'    => $traslado->id,
                    'producto_id'    => $producto->id,
                    'cantidad'       => $cantidadReal,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo'    => $stockNuevo,
                ]);

                InventarioMovimiento::create([
                    'empresa_id'     => $empresa_id,
                    'producto_id'    => $producto->id,
                    'usuario_id'     => auth()->id(),
                    'tipo'           => 'salida',
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo'    => $stockNuevo,
                    'diferencia'     => $stockNuevo - $stockAnterior,
                    'observaciones'  => 'Traslado a ' . $request->local_destino,
                ]);
            }
        });

        return redirect()->route('minimarket.traslados')->with('success', 'Traslado registrado correctamente');
    }
}
