<?php

namespace App\Http\Controllers\Venta;

use App\Helpers\EmpresaHelper;
use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use App\Models\CotizacionDetalle;
use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class CotizacionController extends Controller
{
    public function index()
    {
        $cotizaciones = Cotizacion::where('empresa_id', EmpresaHelper::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Cotizaciones/Index', [
            'cotizaciones' => $cotizaciones,
        ]);
    }

    public function create()
    {
        $clientes  = Cliente::where('empresa_id', EmpresaHelper::id())->orderBy('razon_social')->get();
        $productos = Producto::where('empresa_id', EmpresaHelper::id())->where('activo', true)->orderBy('descripcion')->get();

        return Inertia::render('Cotizaciones/Create', [
            'clientes'  => $clientes,
            'productos' => $productos,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_razon_social' => 'required|max:200',
            'items'                => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $ultimo  = Cotizacion::where('empresa_id', EmpresaHelper::id())->max('numero') ?? 'COT-000000';
            $num     = intval(substr($ultimo, 4)) + 1;
            $numero  = 'COT-' . str_pad($num, 6, '0', STR_PAD_LEFT);

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
                    'linea'               => $i + 1,
                    'producto_id'         => $item['producto_id'] ?? null,
                    'descripcion'         => $item['descripcion'],
                    'unidad_medida'       => $item['unidad_medida'] ?? 'NIU',
                    'cantidad'            => $cantidad,
                    'precio_unitario'     => $precio,
                    'valor_unitario'      => $valorUnitario,
                    'tipo_afectacion_igv' => $item['tipo_afectacion_igv'],
                    'total_valor_venta'   => $totalValor,
                    'total_igv'           => $igvItem,
                    'total'               => $totalItem,
                ];
            }

            $cotizacion = Cotizacion::create([
                'empresa_id'           => EmpresaHelper::id(),
                'usuario_id'           => Auth::id(),
                'cliente_id'           => $request->cliente_id,
                'numero'               => $numero,
                'fecha_emision'        => now()->toDateString(),
                'fecha_vencimiento'    => $request->fecha_vencimiento,
                'cliente_tipo_doc'     => $request->cliente_tipo_doc,
                'cliente_num_doc'      => $request->cliente_num_doc,
                'cliente_razon_social' => strtoupper($request->cliente_razon_social),
                'cliente_direccion'    => $request->cliente_direccion,
                'cliente_email'        => $request->cliente_email,
                'moneda'               => 'PEN',
                'total_gravado'        => round($totalGravado, 2),
                'total_exonerado'      => round($totalExonerado, 2),
                'total_igv'            => round($totalIgv, 2),
                'total'                => round($total, 2),
                'estado'               => 'borrador',
                'observaciones'        => $request->observaciones,
                'terminos_condiciones' => $request->terminos_condiciones,
            ]);

            foreach ($items as $item) {
                CotizacionDetalle::create(array_merge($item, [
                    'cotizacion_id' => $cotizacion->id,
                ]));
            }
        });

        return redirect('/cotizaciones')->with('success', 'Cotizacion creada correctamente.');
    }

    public function show(Cotizacion $cotizacion)
    {
        $cotizacion->load('detalle', 'cliente');
        return Inertia::render('Cotizaciones/Show', [
            'cotizacion' => $cotizacion,
        ]);
    }

   public function convertir(Cotizacion $cotizacion)
    {
        $cotizacion->load('detalle');
        $cotizacion->update(['estado' => 'convertida']);

        // Pasar datos de la cotizacion al POS via sesion
        session([
            'cotizacion_convertir' => [
                'cotizacion_id'        => $cotizacion->id,
                'cotizacion_numero'    => $cotizacion->numero,
                'cliente_id'           => $cotizacion->cliente_id,
                'cliente_tipo_doc'     => $cotizacion->cliente_tipo_doc,
                'cliente_num_doc'      => $cotizacion->cliente_num_doc,
                'cliente_razon_social' => $cotizacion->cliente_razon_social,
                'cliente_direccion'    => $cotizacion->cliente_direccion,
                'cliente_email'        => $cotizacion->cliente_email,
                'items'                => $cotizacion->detalle->map(function($d) {
                    return [
                        'producto_id'         => $d->producto_id,
                        'descripcion'         => $d->descripcion,
                        'unidad_medida'       => $d->unidad_medida,
                        'tipo_afectacion_igv' => $d->tipo_afectacion_igv,
                        'cantidad'            => $d->cantidad,
                        'precio_unitario'     => $d->precio_unitario,
                        'valor_unitario'      => $d->valor_unitario,
                        'total_valor_venta'   => $d->total_valor_venta,
                        'total_igv'           => $d->total_igv,
                        'total'               => $d->total,
                    ];
                })->toArray(),
            ]
        ]);

        return redirect('/ventas/crear')->with('success', 'Cotizacion ' . $cotizacion->numero . ' cargada en el POS.');
    }


    public function destroy(Cotizacion $cotizacion)
    {
        $cotizacion->delete();
        return back()->with('success', 'Cotizacion eliminada.');
    }
public function pdf(Cotizacion $cotizacion)
    {
        $cotizacion->load('detalle');
        $empresa = \App\Models\Empresa::find(\App\Helpers\EmpresaHelper::id());

        $pdf = Pdf::loadView('pdf.cotizacion', [
            'cotizacion' => $cotizacion,
            'empresa'    => $empresa,
        ])->setPaper('a4');

        return $pdf->stream($cotizacion->numero . '.pdf');
    }

public function cambiarEstado(Request $request, Cotizacion $cotizacion)
    {
        $request->validate([
            'estado' => 'required|in:borrador,enviada,aprobada,rechazada,vencida',
        ]);

        $cotizacion->update(['estado' => $request->estado]);

        return back()->with('success', 'Estado actualizado correctamente.');
    }
}