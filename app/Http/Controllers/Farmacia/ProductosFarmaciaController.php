<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\CategoriaMinimarket as Categoria;
use Illuminate\Http\Request;
use App\Models\AuditoriaLog;
use Inertia\Inertia;
use Carbon\Carbon;

class ProductosFarmaciaController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;
        $productos  = Producto::where('empresa_id', $empresa_id)
            ->where('activo', true)
            ->with('categoria')
            ->orderBy('descripcion')
            ->get();
        $categorias = Categoria::where('empresa_id', $empresa_id)->orderBy('nombre')->get();

        $user_rol = auth()->user()->rol;
        return Inertia::render('Farmacia/Productos', compact('productos', 'categorias', 'user_rol'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion'      => 'required|string|max:255',
            'precio_venta'     => 'required|numeric|min:0',
            'stock_actual'     => 'required|integer|min:0',
            'fecha_vencimiento'=> 'nullable|date',
        ]);

        $datos = $request->all();
        if (empty($datos['codigo'])) {
            $datos['codigo'] = 'FAR-' . date('ymdHis');
        }
        $producto = Producto::create([
            ...$datos,
            'empresa_id' => auth()->user()->empresa_id,
            'activo'     => true,
        ]);

        AuditoriaLog::registrar(
            'producto',
            'creado',
            'producto',
            $producto->id,
            $producto->descripcion,
            null,
            $request->only(['descripcion', 'precio_venta', 'precio_compra', 'stock_actual', 'codigo_barras']),
            'Producto creado por ' . auth()->user()->name
        );

        return redirect()->back()->with('success', 'Producto creado correctamente');
    }

    public function update(Request $request, Producto $producto)
    {
        $datosAntes = $producto->only(['descripcion', 'precio_venta', 'precio_compra', 'stock_actual', 'lote', 'fecha_vencimiento', 'codigo_barras']);
        $datos = $request->all();
        if (empty($datos['codigo'])) {
            $datos['codigo'] = $producto->codigo ?: 'FAR-' . str_pad($producto->id, 5, '0', STR_PAD_LEFT);
        }
        $producto->update($datos);
        
        // Detectar cambio importante de precio
        $severidad = 'info';
        $accion = 'editado';
        if (isset($datosAntes['precio_venta']) && (float)$datosAntes['precio_venta'] !== (float)$producto->precio_venta) {
            $accion = 'precio_cambiado';
            $diferencia = abs((float)$producto->precio_venta - (float)$datosAntes['precio_venta']);
            $porcentaje = $datosAntes['precio_venta'] > 0 ? ($diferencia / $datosAntes['precio_venta'] * 100) : 0;
            if ($porcentaje > 30) $severidad = 'warning';
        }
        
        AuditoriaLog::registrar(
            'producto',
            $accion,
            'producto',
            $producto->id,
            $producto->descripcion,
            $datosAntes,
            $producto->only(['descripcion', 'precio_venta', 'precio_compra', 'stock_actual', 'lote', 'fecha_vencimiento']),
            'Producto editado por ' . auth()->user()->name,
            $severidad
        );
        
        return redirect()->back()->with('success', 'Producto actualizado');
    }

    public function destroy(Producto $producto)
    {
        AuditoriaLog::registrar(
            'producto',
            'eliminado',
            'producto',
            $producto->id,
            $producto->descripcion,
            $producto->only(['descripcion', 'precio_venta', 'stock_actual', 'lote']),
            null,
            'Producto eliminado (soft delete) por ' . auth()->user()->name,
            'warning'
        );
        
        $producto->update(['activo' => false]);
        return redirect()->back()->with('success', 'Producto eliminado');
    }

    public function actualizarStock(Request $request, Producto $producto)
    {
        $stockAnterior = $producto->stock_actual;
        $producto->increment('stock_actual', $request->cantidad);
        
        AuditoriaLog::registrar(
            'inventario',
            'ajuste_stock',
            'producto',
            $producto->id,
            $producto->descripcion,
            ['stock' => $stockAnterior],
            ['stock' => $producto->fresh()->stock_actual],
            'Stock ajustado +' . $request->cantidad . ' por ' . auth()->user()->name
        );
        
        return redirect()->back()->with('success', 'Stock actualizado');
    }

    public function kardex(Producto $producto)
    {
        if ($producto->empresa_id !== EmpresaHelper::id()) abort(403);

        // Entradas (compras)
        $entradas = \App\Models\CompraDetalle::with('compra.proveedor')
            ->where('producto_id', $producto->id)
            ->get()
            ->map(fn($d) => [
                'fecha'       => $d->compra->fecha_emision?->format('Y-m-d'),
                'tipo'        => 'ENTRADA',
                'referencia'  => 'Compra ' . $d->compra->numero_comprobante,
                'detalle'     => $d->compra->proveedor?->razon_social ?? '—',
                'lote'        => $d->lote,
                'cantidad'    => floatval($d->cantidad),
                'costo'       => floatval($d->precio_unitario),
                'created_at'  => $d->created_at,
            ]);

        // Salidas (ventas)
        $salidas = \DB::table('venta_detalle')
            ->join('ventas', 'ventas.id', '=', 'venta_detalle.venta_id')
            ->where('venta_detalle.producto_id', $producto->id)
            ->where('ventas.empresa_id', EmpresaHelper::id())
            ->whereNotIn('ventas.estado', ['anulado'])
            ->select(
                'ventas.fecha_emision as fecha',
                'ventas.numero_completo as referencia',
                'ventas.cliente_razon_social as detalle',
                'venta_detalle.lote',
                'venta_detalle.cantidad',
                'venta_detalle.precio_unitario as costo',
                'venta_detalle.created_at'
            )
            ->get()
            ->map(fn($d) => [
                'fecha'      => $d->fecha,
                'tipo'       => 'SALIDA',
                'referencia' => 'Venta ' . $d->referencia,
                'detalle'    => $d->detalle ?? 'Cliente general',
                'lote'       => $d->lote,
                'cantidad'   => floatval($d->cantidad),
                'costo'      => floatval($d->costo),
                'created_at' => $d->created_at,
            ]);

        // Unir y ordenar por fecha
        $movimientos = $entradas->concat($salidas)
            ->sortBy('created_at')
            ->values();

        // Calcular saldo acumulado
        $saldo = 0;
        $kardex = $movimientos->map(function($m) use (&$saldo) {
            if ($m['tipo'] === 'ENTRADA') {
                $saldo += $m['cantidad'];
            } else {
                $saldo -= $m['cantidad'];
            }
            return array_merge($m, ['saldo' => $saldo]);
        });

        return response()->json([
            'producto' => [
                'id'          => $producto->id,
                'descripcion' => $producto->descripcion,
                'stock_actual'=> $producto->stock_actual,
                'lote'        => $producto->lote,
                'fecha_vencimiento' => $producto->fecha_vencimiento,
            ],
            'kardex' => $kardex,
        ]);
    }

    public function historial(Producto $producto)
    {
        if ($producto->empresa_id !== EmpresaHelper::id()) abort(403);

        $historial = \App\Models\CompraDetalle::with('compra.proveedor')
            ->where('producto_id', $producto->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(fn($d) => [
                'fecha'            => $d->compra->fecha_emision?->format('d/m/Y'),
                'numero'           => $d->compra->numero_comprobante,
                'proveedor'        => $d->compra->proveedor?->razon_social,
                'cantidad'         => $d->cantidad,
                'precio_unitario'  => $d->precio_unitario,
                'lote'             => $d->lote,
                'fecha_vencimiento'=> $d->fecha_vencimiento,
                'total'            => $d->total,
            ]);

        return response()->json($historial);
    }

    public function vencimientos()
    {
        $empresa_id = auth()->user()->empresa_id;
        $hoy        = Carbon::today();

        $productos = Producto::where('empresa_id', $empresa_id)
            ->where('activo', true)
            ->whereNotNull('fecha_vencimiento')
            ->get();

        $vencidos    = $productos->filter(fn($p) => Carbon::parse($p->fecha_vencimiento)->lt($hoy))->values();
        $porVencer   = $productos->filter(fn($p) => 
            Carbon::parse($p->fecha_vencimiento)->gte($hoy) && 
            Carbon::parse($p->fecha_vencimiento)->lte($hoy->copy()->addDays(30))
        )->values();
        $porVencer90 = $productos->filter(fn($p) => 
            Carbon::parse($p->fecha_vencimiento)->gt($hoy->copy()->addDays(30)) && 
            Carbon::parse($p->fecha_vencimiento)->lte($hoy->copy()->addDays(90))
        )->values();

        $stockBajo = Producto::where('empresa_id', $empresa_id)
            ->where('activo', true)
            ->whereNotNull('stock_minimo')
            ->whereRaw('stock_actual <= stock_minimo')
            ->orderBy('stock_actual')
            ->get();

        return Inertia::render('Farmacia/Vencimientos', compact('vencidos', 'porVencer', 'porVencer90', 'stockBajo'));
    }

    public function cargarDemo()
    {
        $empresaId = auth()->user()->empresa_id;
        
        // Verificar que no tenga productos ya cargados
        $total = \App\Models\Producto::where('empresa_id', $empresaId)->count();
        if ($total > 0) {
            return back()->with('error', 'Ya tienes ' . $total . ' productos registrados. Limpia el catálogo antes de cargar el demo.');
        }

        try {
            $seeder = new \Database\Seeders\FarmaciaBasicaSeeder();
            $seeder->run($empresaId);
            return back()->with('success', '✅ Medicamentos demo cargados correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

}