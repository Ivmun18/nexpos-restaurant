<?php

namespace App\Http\Controllers\Compra;

use App\Helpers\EmpresaHelper;
use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\AuditService;
use Illuminate\Http\Request;
use App\Models\CajaMovimiento;
use App\Models\SesionCaja;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CompraController extends Controller
{
    public function index()
    {
        $compras = Compra::with('proveedor')
            ->where('empresa_id', EmpresaHelper::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Compras/Index', [
            'compras' => $compras,
        ]);
    }

    public function create()
    {
        $proveedores = Proveedor::where('empresa_id', EmpresaHelper::id())
            ->orderBy('razon_social')
            ->get(['id','tipo_documento','numero_documento','razon_social']);

        $productos = Producto::where('empresa_id', EmpresaHelper::id())
            ->where('activo', true)
            ->orderBy('descripcion')
            ->get(['id','codigo','codigo_barras','descripcion','unidad_medida','precio_compra','tipo_afectacion_igv']);

        return Inertia::render('Compras/Create', [
            'proveedores' => $proveedores,
            'productos'   => $productos,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'proveedor_id'      => 'required',
            'tipo_comprobante'  => 'required|in:01,03',
            'serie_proveedor'   => 'required|max:4',
            'correlativo_proveedor' => 'required',
            'fecha_emision'     => 'required|date',
            'items'             => 'required|array|min:1',
        ]);

        $compra = null;
        DB::transaction(function () use ($request, &$compra) {
            $totalGravado   = 0;
            $totalExonerado = 0;
            $totalIgv       = 0;
            $total          = 0;
            $items          = [];

            foreach ($request->items as $i => $item) {
                $cantidad      = floatval($item['cantidad']);
                $precio        = floatval($item['precio_unitario']);
                $afecto        = $item['tipo_afectacion_igv'] === '10';
                $valorUnitario = $afecto ? round($precio / 1.18, 4) : $precio;
                $totalValor    = round($valorUnitario * $cantidad, 2);
                $igvItem       = $afecto ? round($totalValor * 0.18, 2) : 0;
                $totalItem     = round($precio * $cantidad, 2);

                if ($afecto) {
                    $totalGravado += $totalValor;
                    $totalIgv     += $igvItem;
                } else {
                    $totalExonerado += $totalValor;
                }
                $total += $totalItem;

                $items[] = [
                    'producto_id'         => $item['producto_id'] ?? null,
                    'descripcion'         => $item['descripcion'],
                    'unidad_medida'       => $item['unidad_medida'],
                    'cantidad'            => $cantidad,
                    'precio_unitario'     => $precio,
                    'valor_unitario'      => $valorUnitario,
                    'tipo_afectacion_igv' => $item['tipo_afectacion_igv'],
                    'total_valor'         => $totalValor,
                    'total_igv'           => $igvItem,
                    'total'               => $totalItem,
                    'lote'                => $item['lote'] ?? null,
                    'fecha_vencimiento'   => $item['fecha_vencimiento'] ?? null,
                ];

                // Actualizar stock + lote + vencimiento + precio compra del producto
                if (!empty($item['producto_id'])) {
                    $prod = Producto::find($item['producto_id']);
                    if ($prod) {
                        $prod->increment('stock_actual', $cantidad);
                        // Actualizar con el lote/vencimiento mas reciente
                        $cambios = [];
                        if (!empty($item['lote'])) {
                            $cambios['lote'] = $item['lote'];
                        }
                        if (!empty($item['fecha_vencimiento'])) {
                            $cambios['fecha_vencimiento'] = $item['fecha_vencimiento'];
                        }
                        // Actualizar precio de compra si vino distinto
                        if ($precio > 0 && $precio != $prod->precio_compra) {
                            $cambios['precio_compra'] = $precio;
                        }
                        if (count($cambios) > 0) {
                            $prod->update($cambios);
                        }
                    }
                }
            }

            $compra = Compra::create([
                'empresa_id'            => EmpresaHelper::id(),
                'proveedor_id'          => $request->proveedor_id,
                'usuario_id'            => Auth::id(),
                'tipo_comprobante'      => $request->tipo_comprobante,
                'serie_proveedor'       => strtoupper($request->serie_proveedor),
                'correlativo_proveedor' => $request->correlativo_proveedor,
                'numero_comprobante'    => strtoupper($request->serie_proveedor) . '-' . str_pad($request->correlativo_proveedor, 8, '0', STR_PAD_LEFT),
                'fecha_emision'         => $request->fecha_emision,
                'fecha_vencimiento'     => $request->fecha_vencimiento,
                'moneda'                => 'PEN',
                'tipo_cambio'           => 1,
                'total_gravado'         => round($totalGravado, 2),
                'total_exonerado'       => round($totalExonerado, 2),
                'total_igv'             => round($totalIgv, 2),
                'total'                 => round($total, 2),
                'forma_pago'            => $request->forma_pago ?? 'contado',
                'estado'                => 'recibido',
                'observaciones'         => $request->observaciones,
            ]);

            foreach ($items as $item) {
                CompraDetalle::create(array_merge($item, [
                    'compra_id' => $compra->id,
                ]));
            }
        });

        // Registrar egreso en caja si hay sesión abierta y pago contado
        if (($request->forma_pago ?? 'contado') === 'contado') {
            $sesion = SesionCaja::where('estado', 'abierta')->first();
            if ($sesion) {
                CajaMovimiento::create([
                    'sesion_id'    => $sesion->id,
                    'usuario_id'   => auth()->id(),
                    'tipo'         => 'egreso',
                    'concepto'     => 'Compra ' . strtoupper($request->serie_proveedor) . '-' . str_pad($request->correlativo_proveedor, 8, '0', STR_PAD_LEFT),
                    'referencia_id'=> null,
                    'monto'        => round($compra->total, 2),
                    'observaciones'=> $request->observaciones ?? null,
                ]);
            }
        }

        // 🔐 AUDITORÍA: Registrar creación de compra
        if ($compra) {
            AuditService::registrarCompra($compra, 'create');
        }

        return redirect('/compras')->with('success', 'Compra registrada correctamente.');
    }


    public function crearProductoRapido(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'descripcion'   => 'required|string|max:200',
            'codigo_barras' => 'nullable|string|max:50',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta'  => 'required|numeric|min:0',
        ]);

        $empresaId = auth()->user()->empresa_id;
        $codigo = $request->codigo ?: ('PROD-' . time());

        $producto = \App\Models\Producto::create([
            'empresa_id'          => $empresaId,
            'codigo'              => $codigo,
            'codigo_barras'       => $request->codigo_barras,
            'descripcion'         => $request->descripcion,
            'descripcion_corta'   => mb_substr($request->descripcion, 0, 50),
            'unidad_medida'       => $request->unidad_medida ?? 'NIU',
            'tipo'                => 'producto',
            'precio_venta'        => $request->precio_venta,
            'precio_compra'       => $request->precio_compra,
            'stock_actual'        => 0,
            'stock_minimo'        => $request->stock_minimo ?? 5,
            'tipo_afectacion_igv' => $request->tipo_afectacion_igv ?? '10',
            'controla_stock'      => true,
            'activo'              => true,
            'lote'                => $request->lote,
            'fecha_vencimiento'   => $request->fecha_vencimiento,
            'laboratorio'         => $request->laboratorio,
        ]);

        return response()->json([
            'success' => true,
            'producto' => $producto,
        ]);
    }

    public function show(Compra $compra)
    {
        // 🔐 AUDITORÍA: Registrar visualización
        AuditService::registrar(
            'view',
            'Compras',
            'Compra',
            $compra->id,
            null,
            null,
            "Visto: {$compra->numero_comprobante}"
        );

        $compra->load('detalle', 'proveedor');
        return Inertia::render('Compras/Show', [
            'compra' => $compra,
        ]);
    }

    public function anular(Compra $compra)
    {
        $compraAnterior = $compra->toArray();

        DB::transaction(function () use ($compra) {
            foreach ($compra->detalle as $item) {
                if ($item->producto_id) {
                    Producto::find($item->producto_id)
                        ?->decrement('stock_actual', $item->cantidad);
                }
            }
            $compra->update(['estado' => 'anulado']);
        });

        // 🔐 AUDITORÍA: Registrar anulación
        AuditService::registrar(
            'update',
            'Compras',
            'Compra',
            $compra->id,
            $compraAnterior,
            ['estado' => 'anulado'],
            "Compra anulada: {$compra->numero_comprobante}"
        );

        return back()->with('success', 'Compra anulada correctamente.');
    }
}
