<?php

namespace App\Http\Controllers\Venta;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use App\Helpers\EmpresaHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Services\PdfService;
use App\Services\Sunat\EnvioSunatService;
use App\Models\CajaMovimiento;
use App\Models\SesionCaja;
use App\Mail\ComprobanteEmail;
use Illuminate\Support\Facades\Mail;


class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with('cliente')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
        ]);
    }

    public function create()
    {
        $clientes  = Cliente::where('empresa_id', EmpresaHelper::id())->orderBy('razon_social')->get();
        $productos = Producto::where('empresa_id', EmpresaHelper::id())->where('activo', true)->orderBy('descripcion')->get();

        // Verificar si hay una cotizacion para convertir
        $cotizacion = session('cotizacion_convertir');
        session()->forget('cotizacion_convertir');

        return Inertia::render('Ventas/Create', [
            'clientes'    => $clientes,
            'productos'   => $productos,
            'cotizacion'  => $cotizacion,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_comprobante' => 'required|in:01,03',
            'items'            => 'required|array|min:1',
            'items.*.producto_id'   => 'required',
            'items.*.cantidad'      => 'required|numeric|min:0.001',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Obtener correlativo
            $serie = $request->tipo_comprobante === '01' ? 'F001' : 'B001';
            $ultimo = Venta::where('serie', $serie)->max('correlativo') ?? 0;
            $correlativo = $ultimo + 1;
            $numeroCompleto = $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT);

            // Calcular totales
            $totalGravado   = 0;
            $totalExonerado = 0;
            $totalIgv       = 0;
            $total          = 0;

            $items = [];
            foreach ($request->items as $i => $item) {
                $producto = Producto::find($item['producto_id']);
                $cantidad = floatval($item['cantidad']);
                $precioUnitario = floatval($item['precio_unitario']);

                if ($producto->afecto_igv) {
                    $valorUnitario    = round($precioUnitario / 1.18, 4);
                    $totalValorVenta  = round($valorUnitario * $cantidad, 2);
                    $igvItem          = round($totalValorVenta * 0.18, 2);
                    $totalItem        = round($totalValorVenta + $igvItem, 2);
                    $totalGravado    += $totalValorVenta;
                    $totalIgv        += $igvItem;
                } else {
                    $valorUnitario   = $precioUnitario;
                    $totalValorVenta = round($valorUnitario * $cantidad, 2);
                    $igvItem         = 0;
                    $totalItem       = $totalValorVenta;
                    $totalExonerado += $totalValorVenta;
                }

                $total += $totalItem;

                $items[] = [
                    'linea'              => $i + 1,
                    'producto_id'        => $producto->id,
                    'codigo_producto'    => $producto->codigo,
                    'descripcion'        => $producto->descripcion,
                    'unidad_medida'      => $producto->unidad_medida,
                    'cantidad'           => $cantidad,
                    'precio_unitario'    => $precioUnitario,
                    'valor_unitario'     => $valorUnitario,
                    'descuento_monto'    => 0,
                    'tipo_afectacion_igv'=> $producto->tipo_afectacion_igv,
                    'total_valor_venta'  => $totalValorVenta,
                    'total_igv'          => $igvItem,
                    'total'              => $totalItem,
                ];

                // Descontar stock
                $producto->decrement('stock_actual', $cantidad);
                
            }

            // Crear venta
            $venta = Venta::create([
                'empresa_id' 	       => EmpresaHelper::id(),
                'usuario_id'           => Auth::id(),
                'cliente_id'           => $request->cliente_id,
                'tipo_comprobante'     => $request->tipo_comprobante,
                'serie'                => $serie,
                'correlativo'          => $correlativo,
                'numero_completo'      => $numeroCompleto,
                'fecha_emision'        => now()->toDateString(),
                'hora_emision'         => now()->toTimeString(),
                'cliente_tipo_doc'     => $request->cliente_tipo_doc ?? '0',
                'cliente_num_doc'      => $request->cliente_num_doc,
                'cliente_razon_social' => $request->cliente_razon_social,
                'cliente_direccion'    => $request->cliente_direccion,
                'cliente_email'        => $request->cliente_email,
                'moneda'               => 'PEN',
                'tipo_cambio'          => 1,
                'total_gravado'        => round($totalGravado, 2),
                'total_exonerado'      => round($totalExonerado, 2),
                'total_igv'            => round($totalIgv, 2),
                'total'                => round($total, 2),
                'forma_pago'           => $request->forma_pago ?? 'contado',
                'monto_pagado'         => $request->monto_pagado ?? $total,
                'vuelto'               => max(0, floatval($request->monto_pagado ?? $total) - $total),
                'estado'               => 'emitido',
                'observaciones'        => $request->observaciones,
            ]);

            // Crear detalle
            foreach ($items as $item) {
                VentaDetalle::create(array_merge($item, ['venta_id' => $venta->id]));
            }

// Registrar en caja si hay sesión abierta y el pago es en efectivo
            $sesionCaja = SesionCaja::where('estado', 'abierta')->first();
            if ($sesionCaja) {
                CajaMovimiento::create([
                    'sesion_id'    => $sesionCaja->id,
                    'usuario_id'   => Auth::id(),
                    'tipo'         => 'ingreso',
                    'concepto'     => 'Venta ' . $numeroCompleto,
                    'referencia_id'=> $venta->id,
                    'monto'        => $venta->total,
                ]);
            }
        });

        return redirect('/ventas')->with('success', 'Venta registrada correctamente.');
    }

    public function show(Venta $venta)
    {
        $venta->load('detalle.producto', 'cliente');
        return Inertia::render('Ventas/Show', [
            'venta'   => $venta,
            'empresa' => auth()->user()->empresa,
        ]);
    }

    public function anular(Venta $venta)
    {
        if ($venta->estado === 'anulado') {
            return back()->with('error', 'La venta ya está anulada.');
        }

        DB::transaction(function () use ($venta) {
            // Devolver stock
            foreach ($venta->detalle as $item) {
                if ($item->producto && $item->producto->controla_stock) {
                    $item->producto->increment('stock_actual', $item->cantidad);
                }
            }
            $venta->update(['estado' => 'anulado']);
        });

        return back()->with('success', 'Venta anulada correctamente.');
    }
public function pdfTicket(Venta $venta)
    {
        $venta->load('detalle');
        $pdf = (new PdfService())->generarTicket($venta);
        return $pdf->stream($venta->numero_completo . '_ticket.pdf');
    }

    public function pdfA4(Venta $venta)
    {
        $venta->load('detalle');
        $pdf = (new PdfService())->generarA4($venta);
        return $pdf->stream($venta->numero_completo . '_a4.pdf');
    }
public function enviarSunat(Venta $venta)
    {
        $servicio  = new EnvioSunatService();
        $resultado = $servicio->enviar($venta);

        if ($resultado['exitoso']) {
            return back()->with('success', 'Comprobante enviado a SUNAT correctamente. Código: ' . $resultado['codigo']);
        }

        return back()->with('error', 'Error al enviar a SUNAT: ' . $resultado['descripcion']);
    }
    }
