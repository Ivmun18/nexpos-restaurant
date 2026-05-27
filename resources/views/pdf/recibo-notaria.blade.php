<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pago - {{ $acto->numero_expediente }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding:20px; background:#f5f5f5; }
        .container { max-width:600px; margin:0 auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        .header { background:#14B8A6; color:#fff; padding:20px; text-align:center; }
        .header h1 { font-size:24px; margin:0 0 5px; }
        .header p { font-size:14px; margin:0; opacity:0.95; }
        .content { padding:25px; }
        .row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #eee; }
        .row .label { font-weight:600; color:#666; font-size:13px; }
        .row .value { color:#333; font-size:13px; text-align:right; }
        .expediente { background:#f8f9fa; padding:15px; border-radius:6px; margin-bottom:20px; text-align:center; }
        .expediente-numero { font-size:20px; font-weight:800; color:#14B8A6; margin-bottom:5px; }
        .expediente-tipo { font-size:12px; color:#666; }
        .divider { height:1px; background:#e0e0e0; margin:15px 0; }
        .total-box { background:#f1f5f9; padding:15px; border-radius:6px; margin:15px 0; text-align:center; }
        .total-label { font-size:12px; color:#666; margin-bottom:5px; font-weight:600; }
        .total-valor { font-size:28px; font-weight:800; color:#14B8A6; }
        .saldo-box { background:#fff3cd; padding:12px; border-radius:6px; margin:15px 0; text-align:center; border:2px dashed #ffc107; }
        .saldo-label { font-size:11px; color:#856404; margin-bottom:5px; font-weight:700; }
        .saldo-valor { font-size:24px; font-weight:800; color:#856404; }
        .footer { text-align:center; font-size:11px; color:#888; line-height:1.6; margin-top:20px; }
        .qr-section { background:#f8f9fa; padding:20px; border-radius:8px; margin:20px 0; text-align:center; border:2px solid #14B8A6; }
        .qr-title { font-size:14px; font-weight:700; color:#14B8A6; margin-bottom:10px; }
        .qr-code { margin:15px 0; }
        .qr-code img { width:150px; height:150px; margin:0 auto; }
        .qr-instructions { font-size:11px; color:#666; margin-top:10px; line-height:1.5; }
        .qr-url { font-size:9px; color:#14B8A6; font-weight:600; margin-top:8px; word-break:break-all; }
    </style>
</head>
<body>

<div class="container">
    <!-- HEADER -->
    <div class="header">
        <h1>RECIBO DE PAGO</h1>
        <p>{{ $empresa->razon_social }}</p>
        <p>RUC {{ $empresa->ruc }}</p>
    </div>

    <div class="content">
        <!-- EXPEDIENTE -->
        <div class="expediente">
            <div class="expediente-numero">{{ $acto->numero_expediente }}</div>
            <div class="expediente-tipo">{{ $tiposActo[$acto->tipo_acto] ?? strtoupper($acto->tipo_acto) }}</div>
        </div>

        <!-- DATOS DEL CLIENTE -->
        <div class="row">
            <span class="label">Cliente:</span>
            <span class="value">{{ $acto->cliente->razon_social ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="label">{{ $acto->cliente->tipo_documento == '6' ? 'RUC' : 'DNI' }}:</span>
            <span class="value">{{ $acto->cliente->numero_documento ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="label">Fecha de pago:</span>
            <span class="value">{{ $pago->created_at->format('d/m/Y H:i') }}</span>
        </div>

        <!-- DETALLES DEL PAGO -->
        <div class="divider"></div>
        <div class="row">
            <span class="label">Monto a cobrar:</span>
            <span class="value">S/ {{ number_format($acto->monto_cobrar, 2) }}</span>
        </div>
        <div class="row">
            <span class="label">Monto de este pago:</span>
            <span class="value">S/ {{ number_format($pago->monto, 2) }}</span>
        </div>
        <div class="row">
            <span class="label">Método de pago:</span>
            <span class="value">{{ strtoupper($pago->metodo_pago) }}</span>
        </div>
        @if($pago->referencia)
        <div class="row">
            <span class="label">Referencia:</span>
            <span class="value">{{ $pago->referencia }}</span>
        </div>
        @endif

        <!-- TOTAL PAGADO -->
        <div class="total-box">
            <div class="total-label">MONTO PAGADO</div>
            <div class="total-valor">S/ {{ number_format($pago->monto, 2) }}</div>
        </div>

        <!-- SALDO -->
        @if($saldo > 0)
        <div class="saldo-box">
            <div class="saldo-label">SALDO PENDIENTE</div>
            <div class="saldo-valor">S/ {{ number_format($saldo, 2) }}</div>
        </div>
        @else
        <div class="saldo-box" style="background:#e8f5e9; border-color:#4caf50;">
            <div class="saldo-label" style="color:#2e7d32;">SERVICIO CANCELADO COMPLETAMENTE</div>
        </div>
        @endif

        <!-- CÓDIGO QR PARA CONSULTA -->
        <div class="qr-section">
            <div class="qr-title">📱 CONSULTA TU TRÁMITE EN LÍNEA</div>
            <div class="qr-code">
                <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code">
            </div>
            <div class="qr-instructions">
                <strong>Escanea este código QR</strong> con la cámara de tu celular<br>
                para consultar el estado de tu trámite en cualquier momento
            </div>
            <div class="qr-url">
                O ingresa a: {{ $portalUrl }}
            </div>
        </div>

        <!-- ATENDIDO POR -->
        <div class="divider"></div>
        <div class="row">
            <span class="label">Atendido por:</span>
            <span class="value">{{ $acto->usuario->name ?? '—' }}</span>
        </div>

        <!-- NOTA -->
        <div class="divider"></div>
        <div class="footer">
            <p>* Este recibo es un comprobante provisional.</p>
            <p>El comprobante de pago oficial (Boleta/Factura)</p>
            <p>será emitido al completar el servicio.</p>
            <br>
            <p>Conserve este recibo para cualquier consulta.</p>
            <p>Gracias por su preferencia.</p>
        </div>
    </div>
</div>

</body>
</html>
