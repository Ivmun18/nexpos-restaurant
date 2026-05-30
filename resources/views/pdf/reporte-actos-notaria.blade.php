<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: Arial, sans-serif; font-size:9px; color:#1E293B; padding:16px; }
    h1 { font-size:16px; font-weight:700; color:#1E293B; margin-bottom:4px; }
    h2 { font-size:12px; font-weight:700; color:#4F46E5; margin:16px 0 8px; border-bottom:2px solid #4F46E5; padding-bottom:4px; }
    .info { font-size:9px; color:#64748B; margin-bottom:12px; line-height:1.6; }
    table { width:100%; border-collapse:collapse; margin-bottom:16px; }
    th { background:#1E293B; color:white; padding:6px 8px; text-align:left; font-size:8px; text-transform:uppercase; }
    td { padding:5px 8px; border-bottom:1px solid #E2E8F0; font-size:8px; }
    tr:nth-child(even) { background:#F8FAFC; }
    .totales { background:#EEF2FF !important; font-weight:bold; }
    .totales td { border-top:2px solid #4F46E5; font-size:9px; }
    .right { text-align:right; }
    .badge-pagado { color:#065F46; font-weight:700; }
    .badge-parcial { color:#92400E; font-weight:700; }
    .badge-pendiente { color:#991B1B; font-weight:700; }
    .resumen-box { background:#F8FAFC; border:1px solid #E2E8F0; border-radius:6px; padding:12px; margin:16px 0; display:inline-block; min-width:300px; }
    .resumen-box table { margin:0; }
    .resumen-box td { border:none; padding:3px 12px; }
    .green { color:#166534; }
    .red { color:#991B1B; }
</style>
</head>
<body>
    <h1>📋 Reporte de Actos Notariales</h1>
    <p class="info">
        <strong>{{ $empresa->razon_social }}</strong> — RUC: {{ $empresa->ruc }}<br>
        Periodo: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}<br>
        Generado: {{ now()->format('d/m/Y H:i') }}
    </p>

    <h2>REGISTRO DE ACTOS / EXPEDIENTES</h2>
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Expediente</th>
                <th>Fecha</th>
                <th>Tipo Acto</th>
                <th>Asunto</th>
                <th>DNI/RUC</th>
                <th>Cliente</th>
                <th class="right">Monto</th>
                <th class="right">Pagado</th>
                <th class="right">Saldo</th>
                <th>Pago</th>
                <th>Estado</th>
                <th>Comprobante</th>
            </tr>
        </thead>
        <tbody>
            @foreach($actos as $i => $a)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td style="font-family:monospace; color:#4F46E5;">{{ $a->numero_expediente }}</td>
                <td>{{ $a->fecha_ingreso }}</td>
                <td style="text-transform:capitalize;">{{ str_replace('_', ' ', $a->tipo_acto) }}</td>
                <td style="max-width:120px;">{{ $a->asunto }}</td>
                <td style="font-family:monospace;">{{ $a->cliente_documento ?? '-' }}</td>
                <td>{{ $a->cliente_nombre ?? '-' }}</td>
                <td class="right">S/ {{ number_format($a->monto_cobrar, 2) }}</td>
                <td class="right" style="color:#166534;">S/ {{ number_format($a->monto_pagado, 2) }}</td>
                <td class="right" style="color:{{ $a->saldo > 0 ? '#991B1B' : '#166534' }};">S/ {{ number_format($a->saldo, 2) }}</td>
                <td class="{{ $a->estado_pago === 'pagado' ? 'badge-pagado' : ($a->estado_pago === 'parcial' ? 'badge-parcial' : 'badge-pendiente') }}">
                    {{ ucfirst($a->estado_pago) }}
                </td>
                <td style="text-transform:capitalize;">{{ str_replace('_', ' ', $a->estado) }}</td>
                <td style="font-family:monospace; color:#4F46E5;">{{ $a->comprobante }}</td>
            </tr>
            @endforeach
            <tr class="totales">
                <td colspan="7" class="right">TOTALES:</td>
                <td class="right">S/ {{ number_format($totalCobrar, 2) }}</td>
                <td class="right" style="color:#166534;">S/ {{ number_format($totalPagado, 2) }}</td>
                <td class="right" style="color:#991B1B;">S/ {{ number_format($totalSaldo, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    <h2>RESUMEN</h2>
    <div class="resumen-box">
        <table>
            <tr><td><strong>Total actos:</strong></td><td class="right">{{ count($actos) }}</td></tr>
            <tr><td><strong>Total a cobrar:</strong></td><td class="right">S/ {{ number_format($totalCobrar, 2) }}</td></tr>
            <tr><td><strong>Total cobrado:</strong></td><td class="right green">S/ {{ number_format($totalPagado, 2) }}</td></tr>
            <tr style="border-top:2px solid #1E293B;"><td><strong>Saldo pendiente:</strong></td><td class="right red">S/ {{ number_format($totalSaldo, 2) }}</td></tr>
            <tr><td><strong>Actos pagados:</strong></td><td class="right">{{ $actos->where('estado_pago','pagado')->count() }}</td></tr>
            <tr><td><strong>Actos con saldo:</strong></td><td class="right red">{{ $actos->where('estado_pago','!=','pagado')->count() }}</td></tr>
        </table>
    </div>
</body>
</html>
