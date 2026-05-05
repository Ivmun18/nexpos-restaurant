<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; font-size:12px; color:#1E293B; }
        .header { display:flex; justify-content:space-between; margin-bottom:30px; padding-bottom:20px; border-bottom:2px solid #2563EB; }
        .empresa-nombre { font-size:20px; font-weight:bold; color:#2563EB; }
        .cotizacion-titulo { text-align:right; }
        .cotizacion-numero { font-size:24px; font-weight:bold; color:#1E293B; }
        .seccion { margin-bottom:20px; }
        .seccion-titulo { font-size:11px; font-weight:bold; color:#64748B; text-transform:uppercase; margin-bottom:6px; }
        table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        thead tr { background:#2563EB; color:white; }
        th { padding:8px 12px; text-align:left; font-size:11px; }
        td { padding:8px 12px; border-bottom:1px solid #E2E8F0; font-size:12px; }
        .text-right { text-align:right; }
        .totales { margin-left:auto; width:250px; }
        .totales table td { border:none; }
        .total-final { font-size:16px; font-weight:bold; color:#2563EB; }
        .footer { margin-top:30px; padding-top:20px; border-top:1px solid #E2E8F0; font-size:11px; color:#64748B; }
        .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:bold; background:#EFF6FF; color:#2563EB; }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <div class="empresa-nombre">{{ $empresa->nombre_comercial ?? $empresa->razon_social }}</div>
            <div>RUC: {{ $empresa->ruc }}</div>
            <div>{{ $empresa->direccion }}</div>
            <div>{{ $empresa->telefono }} | {{ $empresa->email }}</div>
        </div>
        <div class="cotizacion-titulo">
            <div style="font-size:13px; color:#64748B; margin-bottom:4px;">COTIZACION</div>
            <div class="cotizacion-numero">{{ $cotizacion->numero }}</div>
            <div style="margin-top:8px;">
                <span class="badge">{{ strtoupper($cotizacion->estado) }}</span>
            </div>
            <div style="margin-top:8px; font-size:11px; color:#64748B;">
                Fecha: {{ $cotizacion->fecha_emision }}<br>
                @if($cotizacion->fecha_vencimiento)
                Vence: {{ $cotizacion->fecha_vencimiento }}
                @endif
            </div>
        </div>
    </div>

    <div class="seccion">
        <div class="seccion-titulo">Cliente</div>
        <div style="font-weight:bold; font-size:14px;">{{ $cotizacion->cliente_razon_social }}</div>
        @if($cotizacion->cliente_num_doc)
        <div>Doc: {{ $cotizacion->cliente_num_doc }}</div>
        @endif
        @if($cotizacion->cliente_direccion)
        <div>Dir: {{ $cotizacion->cliente_direccion }}</div>
        @endif
        @if($cotizacion->cliente_email)
        <div>Email: {{ $cotizacion->cliente_email }}</div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Descripcion</th>
                <th>Unidad</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">P. Unit.</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cotizacion->detalle as $item)
            <tr>
                <td>{{ $item->linea }}</td>
                <td>{{ $item->descripcion }}</td>
                <td>{{ $item->unidad_medida }}</td>
                <td class="text-right">{{ number_format($item->cantidad, 2) }}</td>
                <td class="text-right">S/ {{ number_format($item->precio_unitario, 2) }}</td>
                <td class="text-right">S/ {{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totales">
        <table>
            <tr>
                <td>Op. Gravadas:</td>
                <td class="text-right">S/ {{ number_format($cotizacion->total_gravado, 2) }}</td>
            </tr>
            <tr>
                <td>Op. Exoneradas:</td>
                <td class="text-right">S/ {{ number_format($cotizacion->total_exonerado, 2) }}</td>
            </tr>
            <tr>
                <td>IGV (18%):</td>
                <td class="text-right">S/ {{ number_format($cotizacion->total_igv, 2) }}</td>
            </tr>
            <tr>
                <td class="total-final">TOTAL:</td>
                <td class="text-right total-final">S/ {{ number_format($cotizacion->total, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($cotizacion->observaciones)
    <div class="seccion" style="margin-top:20px;">
        <div class="seccion-titulo">Observaciones</div>
        <div>{{ $cotizacion->observaciones }}</div>
    </div>
    @endif

    @if($cotizacion->terminos_condiciones)
    <div class="seccion">
        <div class="seccion-titulo">Terminos y condiciones</div>
        <div>{{ $cotizacion->terminos_condiciones }}</div>
    </div>
    @endif

    <div class="footer">
        <p>Este documento es una cotizacion y no tiene valor tributario.</p>
        <p>{{ $empresa->razon_social }} - RUC: {{ $empresa->ruc }}</p>
    </div>

</body>
</html>