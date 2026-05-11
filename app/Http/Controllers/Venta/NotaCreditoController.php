<?php

namespace App\Http\Controllers\Venta;

use App\Http\Controllers\Controller;
use App\Models\NotaCredito;
use App\Models\NotaCreditoDetalle;
use App\Models\Venta;
use Illuminate\Http\Request;
use App\Helpers\EmpresaHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class NotaCreditoController extends Controller
{
    public function index()
    {
        $notas = NotaCredito::with('venta')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Ventas/NotasCredito', [
            'notas' => $notas,
        ]);
    }

    public function create(Venta $venta)
    {
        $venta->load('detalle');

        return Inertia::render('Ventas/NotaCreditoCreate', [
            'venta'   => $venta,
            'motivos' => [
                '01' => 'Anulación de la operación',
                '02' => 'Anulación por error en el RUC',
                '03' => 'Corrección por error en la descripción',
                '04' => 'Descuento global',
                '05' => 'Descuento por ítem',
                '06' => 'Devolución total',
                '07' => 'Devolución por ítem',
                '08' => 'Bonificación',
                '09' => 'Disminución en el valor',
                '10' => 'Otros conceptos',
            ],
        ]);
    }

    public function store(Request $request, Venta $venta)
    {
        $request->validate([
            'motivo_codigo'      => 'required|max:2',
            'motivo_descripcion' => 'required|max:200',
            'items'              => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request, $venta) {
            // Serie según tipo de comprobante
            $serie = $venta->tipo_comprobante === '01' ? 'FC01' : 'BC01';
            $ultimo = NotaCredito::where('serie', $serie)->max('correlativo') ?? 0;
            $correlativo   = $ultimo + 1;
            $numeroCompleto = $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT);

            // Calcular totales
            $totalGravado   = 0;
            $totalExonerado = 0;
            $totalIgv       = 0;
            $total          = 0;
            $items          = [];

            foreach ($request->items as $i => $item) {
                $cantidad       = floatval($item['cantidad']);
                $precioUnitario = floatval($item['precio_unitario']);
                $afectoIgv      = $item['tipo_afectacion_igv'] === '10';
                $valorUnitario  = $afectoIgv ? round($precioUnitario / 1.18, 4) : $precioUnitario;
                $totalValor     = round($valorUnitario * $cantidad, 2);
                $igvItem        = $afectoIgv ? round($totalValor * 0.18, 2) : 0;
                $totalItem      = round($precioUnitario * $cantidad, 2);

                if ($afectoIgv) {
                    $totalGravado += $totalValor;
                    $totalIgv     += $igvItem;
                } else {
                    $totalExonerado += $totalValor;
                }
                $total += $totalItem;

                $items[] = [
                    'linea'               => $i + 1,
                    'producto_id'         => $item['producto_id'] ?? null,
                    'descripcion'         => $item['descripcion'],
                    'unidad_medida'       => $item['unidad_medida'],
                    'cantidad'            => $cantidad,
                    'precio_unitario'     => $precioUnitario,
                    'valor_unitario'      => $valorUnitario,
                    'tipo_afectacion_igv' => $item['tipo_afectacion_igv'],
                    'total_valor_venta'   => $totalValor,
                    'total_igv'           => $igvItem,
                    'total'               => $totalItem,
                ];
            }

            $nota = NotaCredito::create([
                'empresa_id'          => EmpresaHelper::id(),
                'usuario_id'          => Auth::id(),
                'venta_id'            => $venta->id,
                'tipo_comprobante'    => '07',
                'serie'               => $serie,
                'correlativo'         => $correlativo,
                'numero_completo'     => $numeroCompleto,
                'fecha_emision'       => now()->toDateString(),
                'doc_ref_tipo'        => $venta->tipo_comprobante,
                'doc_ref_serie'       => $venta->serie,
                'doc_ref_correlativo' => $venta->correlativo,
                'doc_ref_numero'      => $venta->numero_completo,
                'motivo_codigo'       => $request->motivo_codigo,
                'motivo_descripcion'  => $request->motivo_descripcion,
                'cliente_tipo_doc'    => $venta->cliente_tipo_doc,
                'cliente_num_doc'     => $venta->cliente_num_doc,
                'cliente_razon_social'=> $venta->cliente_razon_social,
                'moneda'              => $venta->moneda,
                'tipo_cambio'         => $venta->tipo_cambio,
                'total_gravado'       => round($totalGravado, 2),
                'total_exonerado'     => round($totalExonerado, 2),
                'total_igv'           => round($totalIgv, 2),
                'total'               => round($total, 2),
                'estado'              => 'emitido',
            ]);

            foreach ($items as $item) {
                NotaCreditoDetalle::create(array_merge($item, [
                    'nota_credito_id' => $nota->id,
                ]));
            }

            // Devolver stock si es devolución
            if (in_array($request->motivo_codigo, ['06', '07'])) {
                foreach ($request->items as $item) {
                    if (!empty($item['producto_id'])) {
                        \App\Models\Producto::find($item['producto_id'])
                            ?->increment('stock_actual', $item['cantidad']);
                    }
                }
            }
        });

        return redirect('/notas-credito')->with('success', 'Nota de crédito emitida correctamente.');
    }

    public function show(NotaCredito $notaCredito)
    {
        $notaCredito->load('detalle', 'venta');
        return Inertia::render('Ventas/NotaCreditoShow', [
            'empresa' => auth()->user()->empresa,
            'nota' => $notaCredito,
        ]);
    }
}
