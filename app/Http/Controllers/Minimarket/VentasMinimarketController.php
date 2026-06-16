<?php

namespace App\Http\Controllers\Minimarket;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VentasMinimarketController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $desde = $request->desde ?? now()->startOfMonth()->toDateString();
        $hasta = $request->hasta ?? now()->toDateString();

        $ventas = Venta::with('detalle')
            ->where('empresa_id', $empresaId)
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->orderBy('fecha_emision', 'desc')
            ->paginate(20);

        return Inertia::render('Minimarket/Ventas', [
            'ventas' => $ventas,
            'desde'  => $desde,
            'hasta'  => $hasta,
        ]);
    }

   public function show($id)
{
    $venta = Venta::with('detalle.producto')
        ->where('empresa_id', auth()->user()->empresa_id)
        ->findOrFail($id);

    return Inertia::render('Minimarket/VentaDetalle', [
        'venta'    => $venta,
        'empresa'  => auth()->user()->empresa,
        'imprimir' => session('imprimir', false),
    ]);
}

public function reciboTicket($id)
{
    $empresa = auth()->user()->empresa;
    $venta = Venta::with('detalle')
        ->where('empresa_id', $empresa->id)
        ->findOrFail($id);

    $exonerada = (bool) ($empresa->zona_exonerada ?? false);
    $total = (float) $venta->total;
    $subtotal = $exonerada ? $total : (float) $venta->total_gravado;
    $igv = $exonerada ? 0 : (float) $venta->total_igv;

    $items = $venta->detalle->map(function($d) {
        return [
            'descripcion'     => $d->descripcion,
            'cantidad'        => $d->cantidad,
            'precio_unitario' => $d->precio_unitario,
            'total'           => $d->total,
        ];
    })->toArray();

    $tipoDoc = $venta->tipo_comprobante === '01' ? 'FACTURA ELECTRONICA' : 'BOLETA ELECTRONICA';
    $serie   = $venta->serie;
    $numero  = str_pad($venta->correlativo, 8, '0', STR_PAD_LEFT);
    $fecha   = \Carbon\Carbon::parse($venta->fecha_emision)->format('d/m/Y');
    $hora    = $venta->hora_emision ?? '';

    $totalLetras = $this->numeroALetrasMinimarket($total);

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.recibo-minimarket-ticket', [
        'empresa'          => $empresa,
        'tipoDoc'          => $tipoDoc,
        'tipoComp'         => $venta->tipo_comprobante,
        'serie'            => $serie,
        'numero'           => $numero,
        'fecha'            => $fecha,
        'hora'             => $hora,
        'clienteNombre'    => $venta->cliente_razon_social ?: 'CLIENTES VARIOS',
        'clienteDocumento' => $venta->cliente_num_doc ?: '00000000',
        'clienteTipoDoc'   => $venta->cliente_tipo_doc ?: '1',
        'items'            => $items,
        'subtotal'         => $subtotal,
        'igv'              => $igv,
        'total'            => $total,
        'totalLetras'      => $totalLetras,
        'vendedor'         => auth()->user()->name ?? 'ADMIN',
        'metodoPago'       => $venta->metodo_pago ?? 'efectivo',
        'estadoSunat'      => $venta->nubefact_estado,
    ]);
    $pdf->setPaper([0, 0, 226.77, 1000], 'portrait');

    return $pdf->stream('ticket-' . $serie . '-' . $numero . '.pdf');
}

private function numeroALetrasMinimarket($numero)
{
    $entero  = (int) floor($numero);
    $decimal = (int) round(($numero - $entero) * 100);
    return strtoupper($this->convertirNumeroALetrasMinimarket($entero)) . " CON {$decimal}/100 SOLES";
}

private function convertirNumeroALetrasMinimarket($num)
{
    if ($num == 0) return 'cero';
    $unidades   = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
    $decenas    = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciseis', 'diecisiete', 'dieciocho', 'diecinueve'];
    $decenasMul = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
    $centenas   = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

    if ($num < 10) return $unidades[$num];
    if ($num < 20) return $decenas[$num - 10];
    if ($num < 100) {
        $d = intdiv($num, 10);
        $u = $num % 10;
        return $decenasMul[$d] . ($u > 0 ? ' y ' . $unidades[$u] : '');
    }
    if ($num < 1000) {
        $c = intdiv($num, 100);
        $r = $num % 100;
        $texto = $num == 100 ? 'cien' : $centenas[$c];
        return $texto . ($r > 0 ? ' ' . $this->convertirNumeroALetrasMinimarket($r) : '');
    }
    if ($num < 1000000) {
        $m = intdiv($num, 1000);
        $r = $num % 1000;
        $prefijo = $m == 1 ? 'mil' : $this->convertirNumeroALetrasMinimarket($m) . ' mil';
        return $prefijo . ($r > 0 ? ' ' . $this->convertirNumeroALetrasMinimarket($r) : '');
    }
    return (string) $num;
}
}