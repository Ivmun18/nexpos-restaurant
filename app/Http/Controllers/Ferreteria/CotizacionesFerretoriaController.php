<?php

namespace App\Http\Controllers\Ferreteria;

use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use App\Models\CotizacionDetalle;
use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CotizacionesFerretoriaController extends Controller
{
    public function index()
    {
        $empresa_id   = auth()->user()->empresa_id;
        $cotizaciones = Cotizacion::where('empresa_id', $empresa_id)
            ->with('detalle')
            ->orderByDesc('created_at')
            ->get();
        $clientes  = Cliente::where('empresa_id', $empresa_id)->orderBy('razon_social')->get();
        $productos = Producto::where('empresa_id', $empresa_id)->where('activo', true)->get();
        return Inertia::render('Ferreteria/Cotizaciones', compact('cotizaciones', 'clientes', 'productos'));
    }

    public function store(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $numero = 'COT-' . str_pad(Cotizacion::where('empresa_id', $empresa_id)->count() + 1, 4, '0', STR_PAD_LEFT);

        $total = collect($request->items)->sum(fn($i) => $i['cantidad'] * $i['precio_unitario']);
        $subtotal = $total / 1.18;
        $igv = $total - $subtotal;

        $cotizacion = Cotizacion::create([
            'empresa_id'           => $empresa_id,
            'usuario_id'           => auth()->id(),
            'cliente_id'           => $request->cliente_id ?: null,
            'numero'               => $numero,
            'fecha_emision'        => $request->fecha_emision,
            'fecha_vencimiento'    => $request->fecha_vencimiento,
            'cliente_razon_social' => $request->cliente_razon_social,
            'cliente_num_doc'      => $request->cliente_num_doc,
            'cliente_tipo_doc'     => $request->cliente_tipo_doc,
            'cliente_direccion'    => $request->cliente_direccion,
            'cliente_email'        => $request->cliente_email,
            'moneda'               => 'PEN',
            'total_gravado'        => round($subtotal, 2),
            'total_igv'            => round($igv, 2),
            'total_exonerado'      => 0,
            'total'                => round($total, 2),
            'estado'               => 'borrador',
            'observaciones'        => $request->observaciones,
        ]);

        foreach ($request->items as $linea => $item) {
            CotizacionDetalle::create([
                'cotizacion_id'      => $cotizacion->id,
                'producto_id'        => $item['producto_id'] ?: null,
                'linea'              => $linea + 1,
                'descripcion'        => $item['descripcion'],
                'unidad_medida'      => $item['unidad_medida'] ?? 'UND',
                'cantidad'           => $item['cantidad'],
                'precio_unitario'    => $item['precio_unitario'],
                'valor_unitario'     => $item['precio_unitario'] / 1.18,
                'tipo_afectacion_igv'=> '10',
                'total_valor_venta'  => $item['cantidad'] * $item['precio_unitario'],
                'total'              => $item['cantidad'] * $item['precio_unitario'],
            ]);
        }

        return back()->with('success', 'Cotización creada');
    }

    public function cambiarEstado(Request $request, Cotizacion $cotizacion)
    {
        $cotizacion->update(['estado' => $request->estado]);
        return back()->with('success', 'Estado actualizado');
    }

    public function destroy(Cotizacion $cotizacion)
    {
        $cotizacion->detalles()->delete();
        $cotizacion->delete();
        return back()->with('success', 'Cotización eliminada');
    }
}
