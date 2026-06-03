<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 11px; color: #1E293B; }
    h2 { text-align: center; font-size: 16px; margin: 0; }
    .subtitulo { text-align: center; color: #64748B; font-size: 11px; margin: 4px 0 16px; }
    .empresa { text-align: center; font-size: 12px; font-weight: bold; margin-bottom: 4px; }
    .resumen { display: flex; gap: 10px; margin-bottom: 16px; }
    .card { border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 12px; flex: 1; text-align: center; }
    .card-label { font-size: 10px; color: #64748B; }
    .card-valor { font-size: 16px; font-weight: 700; color: #3B82F6; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background: #1E293B; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; }
    td { padding: 5px 8px; font-size: 10px; border-bottom: 1px solid #F1F5F9; }
    tr:nth-child(even) { background: #F8FAFC; }
    .total-row { font-weight: 700; background: #EFF6FF; }
    .footer { text-align: center; font-size: 9px; color: #94A3B8; margin-top: 20px; }
    .pagado { color: #16A34A; font-weight: 600; }
    .pendiente { color: #DC2626; font-weight: 600; }
</style>
</head>
<body>
    <p class="empresa">{{ $empresa->razon_social }} — RUC: {{ $empresa->ruc }}</p>
    <h2>🏨 Reporte de Estadías Hotel</h2>
    <p class="subtitulo">Período: {{ $desde }} al {{ $hasta }} &nbsp;|&nbsp; Generado: {{ now()->format('d/m/Y H:i') }}</p>

    <table style="width:100%; margin-bottom:16px; border:none;">
        <tr>
            <td style="text-align:center; border:1px solid #E2E8F0; border-radius:6px; padding:8px;">
                <div style="font-size:10px; color:#64748B;">Total Reservas</div>
                <div style="font-size:18px; font-weight:700; color:#3B82F6;">{{ $reservas->count() }}</div>
            </td>
            <td style="text-align:center; border:1px solid #E2E8F0; padding:8px;">
                <div style="font-size:10px; color:#64748B;">Ingresos Totales</div>
                <div style="font-size:18px; font-weight:700; color:#16A34A;">S/ {{ number_format($totalIngresos, 2) }}</div>
            </td>
            <td style="text-align:center; border:1px solid #E2E8F0; padding:8px;">
                <div style="font-size:10px; color:#64748B;">Pendiente Cobro</div>
                <div style="font-size:18px; font-weight:700; color:#DC2626;">S/ {{ number_format($reservas->sum('total') - $totalIngresos, 2) }}</div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Código</th>
                <th>Huésped</th>
                <th>Documento</th>
                <th>Habitación</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Noches</th>
                <th>Precio/noche</th>
                <th>Total</th>
                <th>Pagado</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservas as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->codigo }}</td>
                <td>{{ $r->huesped?->nombre_completo ?? '-' }}</td>
                <td>{{ $r->huesped?->numero_documento ?? '-' }}</td>
                <td>Hab. {{ $r->habitacion?->numero }} — {{ $r->habitacion?->tipo?->nombre }}</td>
                <td>{{ \Carbon\Carbon::parse($r->fecha_checkin)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($r->fecha_checkout_previsto)->format('d/m/Y') }}</td>
                <td style="text-align:center;">{{ $r->num_noches }}</td>
                <td>S/ {{ number_format($r->precio_noche, 2) }}</td>
                <td>S/ {{ number_format($r->total, 2) }}</td>
                <td>S/ {{ number_format($r->monto_pagado, 2) }}</td>
                <td class="{{ $r->estado_pago === 'pagado' ? 'pagado' : 'pendiente' }}">
                    {{ strtoupper($r->estado_pago) }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="9" style="text-align:right;">TOTALES:</td>
                <td>S/ {{ number_format($reservas->sum('total'), 2) }}</td>
                <td>S/ {{ number_format($totalIngresos, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    <p class="footer">Reporte generado por NEXPOS — Sistema Multi-industry</p>
</body>
</html>
