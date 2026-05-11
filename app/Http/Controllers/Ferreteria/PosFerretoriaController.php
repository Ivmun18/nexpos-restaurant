<?php

namespace App\Http\Controllers\Ferreteria;

use App\Http\Controllers\Controller;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\CategoriaMinimarket as Categoria;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\FacturacionService;

class PosFerretoriaController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;

        $cajaAbierta = \App\Models\CajaMinimarket::where('empresa_id', $empresa_id)
            ->where('estado', 'abierta')
            ->latest()->first();

        if (!$cajaAbierta) {
            return redirect()->route('ferreteria.caja')->with('warning', '⚠️ Debes abrir la caja antes de vender.');
        }

        $productos  = Producto::where('empresa_id', $empresa_id)->where('activo', true)->with('categoria')->get();
        $categorias = Categoria::where('empresa_id', $empresa_id)->orderBy('nombre')->get();
        $clientes   = \App\Models\Cliente::where('empresa_id', $empresa_id)->orderBy('razon_social')->get(['id','razon_social','numero_documento','tipo_documento','email']);

        return Inertia::render('Ferreteria/Pos', compact('productos', 'categorias', 'clientes', 'cajaAbierta'));
    }

    public function store(Request $request)
    {
        $empresa        = Empresa::find(auth()->user()->empresa_id);
        $tipoComprobante = $request->tipo_comprobante ?? 'ninguno';

        // Definir tipo y serie
        if ($tipoComprobante === 'factura') {
            $tipo  = '01';
            $serie = $empresa->serie_factura ?? 'F001';
        } else {
            $tipo  = '03';
            $serie = $empresa->serie_boleta ?? 'B001';
        }

        $correlativo = (Venta::where('empresa_id', $empresa->id)
            ->where('serie', $serie)->max('correlativo') ?? 0) + 1;

        // Calcular IGV según régimen
        $esRus = $empresa->regimen_tributario === 'RUS';
        if ($esRus) {
            $igv      = 0;
            $gravado  = 0;
            $exonerado= 0;
            $inafecto = $request->total;
        } elseif ($empresa->zona_exonerada) {
            $igv      = 0;
            $gravado  = 0;
            $exonerado= $request->total;
            $inafecto = 0;
        } else {
            $igv      = round($request->total / 1.18 * 0.18, 2);
            $gravado  = round($request->total - $igv, 2);
            $exonerado= 0;
            $inafecto = 0;
        }

        $venta = Venta::create([
            'empresa_id'          => $empresa->id,
            'usuario_id'          => auth()->id(),
            'tipo_comprobante'    => $tipo,
            'serie'               => $serie,
            'correlativo'         => $correlativo,
            'numero_completo'     => $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT),
            'fecha_emision'       => now()->toDateString(),
            'hora_emision'        => now()->toTimeString(),
            'moneda'              => 'PEN',
            'total_gravado'       => $gravado,
            'total_exonerado'     => $exonerado ?? 0,
            'total_inafecto'      => $inafecto ?? 0,
            'total_igv'           => $igv,
            'total_descuento'     => 0,
            'metodo_pago'         => $request->metodo_pago,
            'total'               => $request->total,
            'cliente_tipo_doc'    => $tipoComprobante === 'factura' ? '6' : '1',
            'cliente_num_doc'     => $request->cliente_dni ?? '',
            'cliente_razon_social'=> $request->cliente_razon_social ?? '',
            'cliente_email'       => $request->cliente_email ?? '',
        ]);

        foreach ($request->items as $index => $item) {
            VentaDetalle::create([
                'venta_id'           => $venta->id,
                'producto_id'        => $item['id'],
                'linea'              => $index + 1,
                'codigo_producto'    => $item['codigo_barras'] ?? $item['codigo'] ?? 'S/C',
                'descripcion'        => $item['descripcion'],
                'unidad_medida'      => 'NIU',
                'cantidad'           => $item['cantidad'],
                'precio_unitario'    => $item['precio_venta'],
                'valor_unitario'     => $item['precio_venta'],
                'descuento_monto'    => 0,
                'tipo_afectacion_igv'=> ($esRus || $empresa->zona_exonerada) ? '30' : '10',
                'total_valor_venta'  => $item['cantidad'] * $item['precio_venta'],
                'total_igv'          => 0,
                'total'              => $item['cantidad'] * $item['precio_venta'],
            ]);

            Producto::where('id', $item['id'])->decrement('stock_actual', $item['cantidad']);
        }

        // Emitir comprobante electrónico
        if ($tipoComprobante !== 'ninguno' && $empresa->nubefact_token) {
            (new FacturacionService())->emitir($venta, $empresa);
        }

        return redirect()->route('ferreteria.ventas.show', $venta->id)
            ->with('success', 'Venta registrada correctamente')
            ->with('imprimir', true);
    }
}
