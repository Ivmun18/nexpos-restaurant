<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: 'Courier New', monospace; font-size:11px; color:#000; width:72mm; }
    .center { text-align:center; }
    .bold { font-weight:bold; }
    .line { border-top:1px dashed #000; margin:6px 0; }
    .row { display:flex; justify-content:space-between; margin:2px 0; }
    .title { font-size:14px; font-weight:bold; text-align:center; }
    .num { font-size:13px; font-weight:bold; text-align:center; }
    .total-row { display:flex; justify-content:space-between; font-weight:bold; font-size:13px; margin:3px 0; }
    table { width:100%; border-collapse:collapse; }
    th { font-size:10px; border-bottom:1px dashed #000; padding:2px 0; text-align:left; }
    td { font-size:10px; padding:2px 0; vertical-align:top; }
    .td-right { text-align:right; }
</style>
</head>
<body>

    <!-- Empresa -->
    <div class="center" style="margin-bottom:6px;">
        <p class="title">MI EMPRESA SAC</p>
        <p>RUC: 20123456789</p>
        <p>Av. Principal 123, Lima</p>
        <p>Tel: 01-1234567</p>
    </div>

    <div class="line"></div>

    <!-- Tipo comprobante -->
    <div class="center" style="margin:6px 0;">
        <p class="bold" style="font-size:12px;">
            {{ $venta->tipo_comprobante === '01' ? 'FACTURA ELECTRÓNICA' : 'BOLETA DE VENTA ELECTRÓNICA' }}
        </p>
        <p class="num">{{ $venta->numero_completo }}</p>
    </div>

    <div class="line"></div>

    <!-- Fecha -->
    <div class="row">
        <span>Fecha:</span>
        <span>{{ \Carbon\Carbon::parse($venta->fecha_emision)->format('d/m/Y') }}</span>
    </div>
    <div class="row">
        <span>Hora:</span>
        <span>{{ $venta->hora_emision }}</span>
    </div>

    <div class="line"></div>

    <!-- Cliente -->
    <p class="bold">Cliente:</p>
    <p>{{ $venta->cliente_razon_social ?? 'VARIOS' }}</p>
    @if($venta->cliente_num_doc)
        <p>{{ $venta->cliente_tipo_doc == '6' ? 'RUC' : 'DNI' }}: {{ $venta->cliente_num_doc }}</p>
    @endif

    <div class="line"></div>

    <!-- Productos -->
    <table>
        <thead>
            <tr>
                <th style="width:40%;">Descripción</th>
                <th style="width:15%; text-align:center;">Cant.</th>
                <th style="width:20%; text-align:right;">P.Unit</th>
                <th style="width:25%; text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalle as $item)
            <tr>
                <td>{{ Str::limit($item->descripcion, 20) }}</td>
                <td style="text-align:center;">{{ number_format($item->cantidad, 2) }}</td>
                <td class="td-right">{{ number_format($item->precio_unitario, 2) }}</td>
                <td class="td-right">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <!-- Totales -->
    <div class="row">
        <span>Op. Gravadas:</span>
        <span>S/ {{ number_format($venta->total_gravado, 2) }}</span>
    </div>
    @if($venta->total_exonerado > 0)
    <div class="row">
        <span>Op. Exoneradas:</span>
        <span>S/ {{ number_format($venta->total_exonerado, 2) }}</span>
    </div>
    @endif
    <div class="row">
        <span>IGV (18%):</span>
        <span>S/ {{ number_format($venta->total_igv, 2) }}</span>
    </div>

    <div class="line"></div>

    <div class="total-row">
        <span>TOTAL:</span>
        <span>S/ {{ number_format($venta->total, 2) }}</span>
    </div>

    @if($venta->monto_pagado > 0)
    <div class="row">
        <span>Efectivo:</span>
        <span>S/ {{ number_format($venta->monto_pagado, 2) }}</span>
    </div>
    <div class="row">
        <span>Vuelto:</span>
        <span>S/ {{ number_format($venta->vuelto, 2) }}</span>
    </div>
    @endif

    <div class="line"></div>

    <!-- Pie -->
    <div class="center" style="margin-top:8px;">
        <p>Representación impresa de la</p>
        <p>{{ $venta->tipo_comprobante === '01' ? 'FACTURA' : 'BOLETA' }} ELECTRÓNICA</p>
        <p style="margin-top:4px;">¡Gracias por su compra!</p>
        <p style="margin-top:8px; font-size:10px;">Autorizado mediante Resolución</p>
        <p style="font-size:10px;">de Superintendencia N° 097-2012</p>
    </div>

</body>
</html>