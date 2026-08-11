<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; }
    .header { padding: 20px; border-bottom: 3px solid #EA580C; margin-bottom: 16px; }
    .empresa-nombre { font-size: 18px; font-weight: bold; color: #EA580C; }
    .empresa-info { font-size: 10px; color: #64748B; margin-top: 3px; }
    .titulo { font-size: 14px; font-weight: bold; color: #1E293B; margin-top: 8px; }
    .periodo { font-size: 11px; color: #64748B; }
    .kpis { display: flex; gap: 10px; margin-bottom: 16px; padding: 0 20px; }
    .kpi { flex: 1; border: 1px solid #E2E8F0; border-radius: 6px; padding: 10px; text-align: center; }
    .kpi-val { font-size: 14px; font-weight: bold; color: #EA580C; }
    .kpi-lab { font-size: 9px; color: #64748B; text-transform: uppercase; margin-top: 2px; }
    .section { padding: 0 20px; margin-bottom: 16px; }
    .section-title { font-size: 12px; font-weight: bold; color: #EA580C; border-bottom: 1px solid #EA580C; padding-bottom: 4px; margin-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; font-size: 10px; }
    th { background: #1E293B; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; }
    td { padding: 6px 8px; border-bottom: 1px solid #F1F5F9; }
    tr:nth-child(even) { background: #FFF7ED; }
    .total-row { background: #FFF7ED !important; font-weight: bold; border-top: 2px solid #EA580C; }
    .badge-boleta { background: #DCFCE7; color: #166534; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
    .badge-factura { background: #DBEAFE; color: #1E40AF; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
    .badge-recibo { background: #F3F4F6; color: #374151; padding: 2px 6px; border-radius: 4px; font-size: 9px; }
    .resumen-grid { display: flex; gap: 10px; margin-bottom: 16px; padding: 0 20px; }
    .resumen-box { flex: 1; border: 1px solid #E2E8F0; border-radius: 6px; padding: 10px; }
    .resumen-titulo { font-size: 10px; font-weight: bold; color: #64748B; text-transform: uppercase; margin-bottom: 6px; }
    .resumen-fila { display: flex; justify-content: space-between; margin-bottom: 3px; font-size: 10px; }
    .footer { text-align: center; color: #94A3B8; font-size: 9px; padding: 12px 20px; border-top: 1px solid #E2E8F0; margin-top: 16px; }
</style>
</head>
<body>

<div class="header">
    <div class="empresa-nombre">{{ $empresa->nombre_comercial ?? $empresa->razon_social }}</div>
    <div class="empresa-info">
        RUC: {{ $empresa->ruc }} &nbsp;|&nbsp;
        {{ $empresa->direccion }} &nbsp;|&nbsp;
        Tel: {{ $empresa->telefono }}
    </div>
    <div class="titulo">📊 REPORTE CONTABLE DE INGRESOS</div>
    <div class="periodo">Período: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }} &nbsp;|&nbsp; Generado: {{ now()->format('d/m/Y H:i') }}</div>
</div>

<!-- KPIs -->
<div class="kpis">
    <div class="kpi">
        <div class="kpi-val">S/ {{ number_format($totalGeneral, 2) }}</div>
        <div class="kpi-lab">Total Ingresos</div>
    </div>
    <div class="kpi">
        <div class="kpi-val">{{ $pagos->count() }}</div>
        <div class="kpi-lab">Transacciones</div>
    </div>
    <div class="kpi">
        <div class="kpi-val">S/ {{ number_format($totalMembresias, 2) }}</div>
        <div class="kpi-lab">Membresías</div>
    </div>
    <div class="kpi">
        <div class="kpi-val">S/ {{ number_format($totalSesiones, 2) }}</div>
        <div class="kpi-lab">Sesiones</div>
    </div>
</div>

<!-- Resumen por método -->
<div class="resumen-grid">
    <div class="resumen-box">
        <div class="resumen-titulo">💳 Por Método de Pago</div>
        <div class="resumen-fila"><span>💵 Efectivo:</span><b>S/ {{ number_format($totalEfectivo, 2) }}</b></div>
        <div class="resumen-fila"><span>📱 Yape/Plin:</span><b>S/ {{ number_format($totalYape, 2) }}</b></div>
        <div class="resumen-fila"><span>🏦 Transferencia:</span><b>S/ {{ number_format($totalTransferencia, 2) }}</b></div>
    </div>
    <div class="resumen-box">
        <div class="resumen-titulo">📋 Por Tipo</div>
        <div class="resumen-fila"><span>Membresías:</span><b>S/ {{ number_format($totalMembresias, 2) }}</b></div>
        <div class="resumen-fila"><span>Sesiones diarias:</span><b>S/ {{ number_format($totalSesiones, 2) }}</b></div>
        <div class="resumen-fila" style="border-top:1px solid #E2E8F0; padding-top:3px; margin-top:3px;"><span><b>TOTAL:</b></span><b style="color:#EA580C;">S/ {{ number_format($totalGeneral, 2) }}</b></div>
    </div>
</div>

<!-- Detalle de pagos -->
<div class="section">
    <div class="section-title">📋 DETALLE DE PAGOS</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>DNI</th>
                <th>Plan / Concepto</th>
                <th>Comprobante</th>
                <th>Método</th>
                <th style="text-align:right;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagos as $i => $pago)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td>
                <td><b>{{ $pago->miembro?->nombre }} {{ $pago->miembro?->apellidos }}</b></td>
                <td>{{ $pago->miembro?->dni ?? '-' }}</td>
                <td>{{ $pago->plan?->nombre ?? 'Sesión diaria' }}</td>
                <td>
                    @if($pago->tipo_comprobante === 'boleta')
                        <span class="badge-boleta">BOLETA</span>
                    @elseif($pago->tipo_comprobante === 'factura')
                        <span class="badge-factura">FACTURA</span>
                    @else
                        <span class="badge-recibo">RECIBO</span>
                    @endif
                </td>
                <td style="text-transform:capitalize;">{{ $pago->metodo_pago }}</td>
                <td style="text-align:right; font-weight:bold;">S/ {{ number_format($pago->monto, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="7"><b>TOTAL GENERAL</b></td>
                <td style="text-align:right; font-size:12px; color:#EA580C;"><b>S/ {{ number_format($totalGeneral, 2) }}</b></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="footer">
    NEXPOS · Sistema de Gestión · nexposolution.com &nbsp;|&nbsp; Reporte generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }}
</div>

</body>
</html>
