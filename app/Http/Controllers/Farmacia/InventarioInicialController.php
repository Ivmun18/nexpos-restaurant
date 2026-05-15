<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\InventarioMovimiento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventarioInicialController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        // Productos ajustados hoy
        $ajustadosHoy = InventarioMovimiento::where('empresa_id', $empresaId)
            ->where('tipo', 'inicial')
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $totalProductos = Producto::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->count();

        return Inertia::render('Farmacia/InventarioInicial', [
            'ajustados_hoy'   => $ajustadosHoy,
            'total_productos' => $totalProductos,
        ]);
    }

    /**
     * Busca producto por código de barras o nombre
     */
    public function buscar(Request $request)
    {
        $request->validate(['codigo' => 'required|string|max:100']);

        $empresaId = auth()->user()->empresa_id;
        $codigo = trim($request->codigo);

        $producto = Producto::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->where(function($q) use ($codigo) {
                $q->where('codigo_barras', $codigo)
                  ->orWhere('descripcion', 'LIKE', "%{$codigo}%");
            })
            ->first(['id', 'descripcion', 'codigo_barras', 'precio_venta', 'precio_compra', 'stock_actual', 'stock_minimo', 'lote', 'fecha_vencimiento', 'laboratorio', 'principio_activo', 'presentacion', 'concentracion']);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => "No se encontró producto con código: {$codigo}"
            ], 404);
        }

        return response()->json([
            'success' => true,
            'producto' => $producto,
        ]);
    }

    /**
     * Actualiza stock, lote, vencimiento y precio de un producto
     */
    public function actualizar(Request $request)
    {
        $request->validate([
            'producto_id'       => 'required|exists:productos,id',
            'stock_nuevo'       => 'required|numeric|min:0',
            'lote'              => 'nullable|string|max:50',
            'fecha_vencimiento' => 'nullable|date',
            'precio_venta'      => 'nullable|numeric|min:0',
            'precio_compra'     => 'nullable|numeric|min:0',
        ]);

        $empresaId = auth()->user()->empresa_id;
        $producto = Producto::where('empresa_id', $empresaId)
            ->where('id', $request->producto_id)
            ->firstOrFail();

        $stockAnterior = $producto->stock_actual;
        $diferencia = $request->stock_nuevo - $stockAnterior;

        $datosUpdate = [
            'stock_actual' => $request->stock_nuevo,
        ];
        if ($request->filled('lote'))              $datosUpdate['lote'] = $request->lote;
        if ($request->filled('fecha_vencimiento')) $datosUpdate['fecha_vencimiento'] = $request->fecha_vencimiento;
        if ($request->filled('precio_venta'))      $datosUpdate['precio_venta'] = $request->precio_venta;
        if ($request->filled('precio_compra'))     $datosUpdate['precio_compra'] = $request->precio_compra;

        $producto->update($datosUpdate);

        // Registrar movimiento de auditoría
        InventarioMovimiento::create([
            'empresa_id'        => $empresaId,
            'producto_id'       => $producto->id,
            'usuario_id'        => auth()->id(),
            'tipo'              => 'inicial',
            'stock_anterior'    => $stockAnterior,
            'stock_nuevo'       => $request->stock_nuevo,
            'diferencia'        => $diferencia,
            'lote'              => $request->lote,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'observaciones'     => 'Inventario inicial por escaneo',
        ]);

        return response()->json([
            'success' => true,
            'message' => "✅ {$producto->descripcion} actualizado",
            'producto' => $producto->fresh(),
        ]);
    }
}
