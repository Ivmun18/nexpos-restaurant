<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: Arial, sans-serif; font-size:11px; color:#1E293B; padding:20px; }
    .header { display:flex; justify-content:space-between; align-items:start; margin-bottom:20px; }
    .empresa-nombre { font-size:18px; font-weight:bold; color:#1E293B; }
    .empresa-info { font-size:10px; color:#64748B; margin-top:4px; line-height:1.6; }
    .comprobante-box { border:2px solid #2563EB; border-radius:8px; padding:12px 16px; text-align:center; min-width:200px; }
    .comprobante-tipo { font-size:13px; font-weight:bold; color:#2563EB; }
    .comprobante-num { font-size:15px; font-weight:bold; color:#1E293B; margin-top:4px; }
    .section { margin-bottom:16px; }
    .section-title { font-size:10px; font-weight:bold; color:#94A3B8; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; }
    .cliente-box { background:#F8FAFC; border-radius:6px; padding:10px 14px; }
    .cliente-nombre { font-size:13px; font-weight:bold; color:#1E293B; }
    .cliente-info { font-size:10px; color:#64748B; margin-top:3px; line-height:1.6; }
    table { width:100%; border-collapse:collapse; margin-bottom:16px; }
    thead tr { background:#2563EB; }
    thead th { padding:8px 10px; text-align:left; font-size:10px; color:white; font-weight:600; }
    tbody tr { border-bottom:1px solid #F1F5F9; }
    tbody tr:nth-child(even) { background:#F8FAFC; }
    tbody td { padding:8px 10px; font-size:11px; color:#1E293B; }
    .td-right { text-align:right; }
    .td-center { text-align:center; }
    .totales { display:flex; justify-content:flex-end; }
    .totales-box { width:260px; }
    .totales-row { display:flex; justify-content:space-between; padding:4px 0; font-size:11px; }
    .totales-final { display:flex; justify-content:space-between; padding:8px 0; font-size:14px; font-weight:bold; border-top:2px solid #2563EB; margin-top:4px; color:#2563EB; }
    .footer { margin-top:30px; border-top:1px solid #E2E8F0; padding-top:12px; text-align:center; font-size:9px; color:#94A3B8; line-height:1.8; }
    .badge { display:inline-block; padding:2px 8px; border-radius:20px; font-size:10px; font-weight:bold; }
    .badge-factura { background:#EFF6FF; color:#2563EB; }
    .badge-boleta { background:#F0FDF4; color:#166534; }
    .pago-box { background:#F0FDF4; border-radius:6px; padding:10px 14px; margin-top:12px; }
</style>
</head>
<body>

    <!-- Encabezado -->
    <div class="header">
        <div>
            <p class="empresa-nombre">MI EMPRESA SAC</p>
            <div class="empresa-info">
                <p>RUC: 20123456789</p>
                <p>Av. Principal 123, Lima, Perú</p>
                <p>Tel: 01-1234567 | Email: ventas@miempresa.com</p>
            </div>
        </div>
        <div class="comprobante-box">
            <p class="comprobante-tipo">
                {{ $venta->tipo_comprobante === '01' ? 'FACTURA ELECTRÓNICA' : 'BOLETA DE VENTA ELECTRÓNICA' }}
            </p>
            <p class="comprobante-num">{{ $venta->numero_completo }}</p>
            <p style="font-size:10px; color:#64748B; margin-top:6px;">
                Fecha: {{ \Carbon\Carbon::parse($venta->fecha_emision)->format('d/m/Y') }}
            </p>
            <p style="font-size:10px; color:#64748B;">
                Hora: {{ $venta->hora_emision }}
            </p>
        </div>
    </div>

    <!-- Cliente -->
    <div class="section">
        <p class="section-title">Datos del cliente</p>
        <div class="cliente-box">
            <p class="cliente-nombre">{{ $venta->cliente_razon_social ?? 'CLIENTES VARIOS' }}</p>
            <div class="cliente-info">
                @if($venta->cliente_num_doc)
                    <p>{{ $venta->cliente_tipo_doc == '6' ? 'RUC' : 'DNI' }}: {{ $venta->cliente_num_doc }}</p>
                @endif
                @if($venta->cliente_direccion)
                    <p>Dirección: {{ $venta->cliente_direccion }}</p>
                @endif
                @if($venta->cliente_email)
                    <p>Email: {{ $venta->cliente_email }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Detalle -->
    <div class="section">
        <p class="section-title">Detalle</p>
        <table>
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:10%;">Código</th>
                    <th style="width:35%;">Descripción</th>
                    <th style="width:10%;" class="td-center">Und.</th>
                    <th style="width:10%;" class="td-center">Cant.</th>
                    <th style="width:12%;" class="td-right">V. Unit.</th>
                    <th style="width:8%;" class="td-right">IGV</th>
                    <th style="width:10%;" class="td-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalle as $item)
                <tr>
                    <td>{{ $item->linea }}</td>
                    <td>{{ $item->codigo_producto }}</td>
                    <td>{{ $item->descripcion }}</td>
                    <td class="td-center">{{ $item->unidad_medida }}</td>
                    <td class="td-center">{{ number_format($item->cantidad, 2) }}</td>
                    <td class="td-right">{{ number_format($item->valor_unitario, 2) }}</td>
                    <td class="td-right">{{ number_format($item->total_igv, 2) }}</td>
                    <td class="td-right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totales -->
    <div class="totales">
        <div class="totales-box">
            <div class="totales-row">
                <span style="color:#64748B;">Op. Gravadas:</span>
                <span>S/ {{ number_format($venta->total_gravado, 2) }}</span>
            </div>
            @if($venta->total_exonerado > 0)
            <div class="totales-row">
                <span style="color:#64748B;">Op. Exoneradas:</span>
                <span>S/ {{ number_format($venta->total_exonerado, 2) }}</span>
            </div>
            @endif
            <div class="totales-row">
                <span style="color:#64748B;">IGV (18%):</span>
                <span>S/ {{ number_format($venta->total_igv, 2) }}</span>
            </div>
            <div class="totales-final">
                <span>TOTAL A PAGAR:</span>
                <span>S/ {{ number_format($venta->total, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Pago -->
    <div class="pago-box" style="margin-top:16px;">
        <div style="display:flex; justify-content:space-between; font-size:11px;">
            <span style="color:#166534; font-weight:bold;">Forma de pago: {{ strtoupper($venta->forma_pago) }}</span>
            <span>Efectivo: S/ {{ number_format($venta->monto_pagado, 2) }}</span>
            <span>Vuelto: S/ {{ number_format($venta->vuelto, 2) }}</span>
        </div>
    </div>

    <!-- Pie -->
    <div class="footer">
        <p>Representación impresa de la {{ $venta->tipo_comprobante === '01' ? 'FACTURA' : 'BOLETA' }} ELECTRÓNICA</p>
        <p>Autorizado mediante Resolución de Superintendencia N° 097-2012/SUNAT</p>
        <p>Para verificar este documento ingrese a: https://e-factura.sunat.gob.pe</p>
    </div>

</body>
</html>