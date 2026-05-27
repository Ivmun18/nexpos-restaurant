<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Reporte para Contador</title>
<style>
    body { font-family: sans-serif; font-size: 10px; color: #1E293B; margin: 20px; }
    h1 { font-size: 16px; margin: 0 0 4px; }
    h2 { font-size: 13px; color: #4F46E5; margin: 20px 0 8px; border-bottom: 2px solid #4F46E5; padding-bottom: 4px; }
    .info { font-size: 10px; color: #64748B; margin: 0 0 12px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    th { background: #4F46E5; color: white; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
    td { padding: 5px 8px; border-bottom: 1px solid #E2E8F0; font-size: 9px; }
    tr:nth-child(even) { background: #F8FAFC; }
    .totales { background: #EEF2FF !important; font-weight: bold; }
    .totales td { border-top: 2px solid #4F46E5; font-size: 10px; }
    .right { text-align: right; }
    .resumen-box { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 12px; margin: 16px 0; display: inline-block; }
    .resumen-box table { margin: 0; }
    .resumen-box td { border: none; padding: 3px 12px; }
    .green { color: #166534; }
    .red { color: #991B1B; }
    .page-break { page-break-before: always; }
</style>
</head>
<body>
    <h1>📊 Reporte {{ $nombreIndustria }}</h1>
    <p class="info">
        <strong>{{ $empresa->razon_social ?? 'EMPRESA' }}</strong> — RUC: {{ $empresa->ruc ?? '-' }}<br>
        Periodo: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}<br>
        Generado: {{ now()->format('d/m/Y H:i') }}
    </p>

    <!-- ========== VENTAS ========== -->
    <h2>REGISTRO DE VENTAS</h2>
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Comprobante</th>
                <th>Serie-Número</th>
                <th class="right">Subtotal</th>
                <th class="right">IGV 18%</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventas as $i => $v)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $v->fecha }}</td>
                <td>{{ $v->cliente }}</td>
                <td>{{ $tiposComp[$v->comprobante] ?? $v->comprobante ?? 'S/C' }}</td>
                <td>{{ $v->serie_numero }}</td>
                <td class="right">{{ number_format($v->subtotal_sin_igv, 2) }}</td>
                <td class="right">{{ number_format($v->igv, 2) }}</td>
                <td class="right">{{ number_format($v->total, 2) }}</td>
            </tr>
            @endforeach
            <tr class="totales">
                <td colspan="5" class="right">TOTALES VENTAS:</td>
                <td class="right">S/ {{ number_format($totalSubVentas, 2) }}</td>
                <td class="right">S/ {{ number_format($totalIgvVentas, 2) }}</td>
                <td class="right">S/ {{ number_format($totalVentas, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- ========== COMPRAS ========== -->
    <div class="page-break"></div>
    <h2>REGISTRO DE COMPRAS</h2>
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>RUC</th>
                <th>Comprobante</th>
                <th>Serie-Número</th>
                <th class="right">Gravado</th>
                <th class="right">IGV 18%</th>
                <th class="right">Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($compras as $i => $c)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $c->fecha_emision->format('d/m/Y') }}</td>
                <td>{{ optional($c->proveedor)->razon_social ?? optional($c->proveedor)->nombre ?? '-' }}</td>
                <td>{{ optional($c->proveedor)->ruc ?? '-' }}</td>
                <td>{{ $tiposComp[$c->tipo_comprobante] ?? $c->tipo_comprobante }}</td>
                <td>{{ $c->serie_proveedor ? $c->serie_proveedor . '-' . $c->correlativo_proveedor : ($c->numero_comprobante ?? '-') }}</td>
                <td class="right">{{ number_format($c->total_gravado, 2) }}</td>
                <td class="right">{{ number_format($c->total_igv, 2) }}</td>
                <td class="right">{{ number_format($c->total, 2) }}</td>
                <td>{{ ucfirst($c->estado) }}</td>
            </tr>
            @endforeach
            <tr class="totales">
                <td colspan="6" class="right">TOTALES COMPRAS:</td>
                <td class="right">S/ {{ number_format($totalGravadoCompras, 2) }}</td>
                <td class="right">S/ {{ number_format($totalIgvCompras, 2) }}</td>
                <td class="right">S/ {{ number_format($totalCompras, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- ========== RESUMEN ========== -->
    <h2>RESUMEN DEL PERIODO</h2>
    <div class="resumen-box">
        <table>
            <tr><td><strong>Total Ventas:</strong></td><td class="right green"><strong>S/ {{ number_format($totalVentas, 2) }}</strong></td></tr>
            <tr><td><strong>Total Compras:</strong></td><td class="right red"><strong>S/ {{ number_format($totalCompras, 2) }}</strong></td></tr>
            <tr style="border-top:2px solid #1E293B;"><td><strong>Utilidad Bruta:</strong></td><td class="right" style="color:{{ ($totalVentas - $totalCompras) >= 0 ? '#166534' : '#991B1B' }};"><strong>S/ {{ number_format($totalVentas - $totalCompras, 2) }}</strong></td></tr>
            <tr><td><strong>IGV Ventas:</strong></td><td class="right">S/ {{ number_format($totalIgvVentas, 2) }}</td></tr>
            <tr><td><strong>IGV Compras (crédito fiscal):</strong></td><td class="right">S/ {{ number_format($totalIgvCompras, 2) }}</td></tr>
            <tr><td><strong>IGV a pagar:</strong></td><td class="right" style="color:#991B1B;"><strong>S/ {{ number_format($totalIgvVentas - $totalIgvCompras, 2) }}</strong></td></tr>
        </table>
    </div>
</body>
</html>
