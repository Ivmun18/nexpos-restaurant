<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a1a; width: 100%; }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    .divider { border-top: 1px dashed #999; margin: 6px 0; }
    .divider-solid { border-top: 2px solid #1a1a1a; margin: 6px 0; }
    .header { text-align: center; margin-bottom: 8px; }
    .titulo-notaria { font-size: 13px; font-weight: bold; text-transform: uppercase; }
    .subtitulo { font-size: 10px; color: #444; }
    .recibo-titulo { background: #1a1a1a; color: white; text-align: center; padding: 5px; font-size: 12px; font-weight: bold; margin: 8px 0; }
    .tipo-pago { background: #f0f0f0; text-align: center; padding: 4px; font-size: 11px; font-weight: bold; margin: 4px 0; }
    .row { display: flex; justify-content: space-between; margin: 3px 0; }
    .label { color: #555; }
    .value { font-weight: bold; text-align: right; }
    .monto-box { border: 2px solid #1a1a1a; padding: 8px; text-align: center; margin: 8px 0; }
    .monto-label { font-size: 10px; color: #555; }
    .monto-valor { font-size: 20px; font-weight: bold; }
    .saldo-box { border: 1px dashed #999; padding: 6px; text-align: center; margin: 6px 0; background: #f9f9f9; }
    .saldo-label { font-size: 10px; color: #555; }
    .saldo-valor { font-size: 14px; font-weight: bold; color: #cc0000; }
    .footer { text-align: center; font-size: 9px; color: #777; margin-top: 10px; }
    .num-recibo { font-size: 10px; color: #555; }
    .expediente { font-size: 13px; font-weight: bold; color: #1a1a1a; }
    .metodo { display: inline-block; background: #e8f5e9; padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
</style>
</head>
<body>

<!-- HEADER NOTARÍA -->
<div class="header">
    <div class="titulo-notaria">{{ $empresa->razon_social ?? 'Notaría' }}</div>
    @if($empresa->direccion)
    <div class="subtitulo">{{ $empresa->direccion }}</div>
    @endif
    @if($empresa->telefono)
    <div class="subtitulo">Tel: {{ $empresa->telefono }}</div>
    @endif
    @if($empresa->ruc)
    <div class="subtitulo">RUC: {{ $empresa->ruc }}</div>
    @endif
</div>

<div class="divider-solid"></div>

<!-- TÍTULO RECIBO -->
<div class="recibo-titulo">RECIBO DE PAGO PROVISIONAL</div>
<div class="tipo-pago">{{ $tipo_pago }}</div>

<!-- DATOS RECIBO -->
<div class="num-recibo center">N° Recibo: {{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }}</div>
<div class="num-recibo center">Fecha: {{ $pago->created_at->format('d/m/Y H:i') }}</div>

<div class="divider"></div>

<!-- EXPEDIENTE -->
<div class="row">
    <span class="label">Expediente:</span>
    <span class="expediente">{{ $acto->numero_expediente }}</span>
</div>
<div class="row">
    <span class="label">Tipo:</span>
    <span class="value">{{ $tipo_acto }}</span>
</div>
<div style="margin: 3px 0;">
    <span class="label">Asunto: </span>
    <span>{{ $acto->asunto }}</span>
</div>
@if($acto->partes_intervinientes)
<div style="margin: 3px 0;">
    <span class="label">Partes: </span>
    <span>{{ $acto->partes_intervinientes }}</span>
</div>
@endif

<div class="divider"></div>

<!-- MONTO PAGADO -->
<div class="monto-box">
    <div class="monto-label">MONTO RECIBIDO</div>
    <div class="monto-valor">S/ {{ number_format($pago->monto, 2) }}</div>
    <div class="metodo">{{ strtoupper($pago->metodo_pago) }}</div>
</div>

<!-- RESUMEN FINANCIERO -->
<div class="row">
    <span class="label">Total del servicio:</span>
    <span class="value">S/ {{ number_format($acto->monto_cobrar, 2) }}</span>
</div>
<div class="row">
    <span class="label">Total pagado:</span>
    <span class="value">S/ {{ number_format($acto->monto_pagado, 2) }}</span>
</div>

<div class="divider"></div>

<!-- SALDO PENDIENTE -->
@if($saldo > 0)
<div class="saldo-box">
    <div class="saldo-label">SALDO PENDIENTE</div>
    <div class="saldo-valor">S/ {{ number_format($saldo, 2) }}</div>
</div>
@else
<div class="saldo-box" style="background:#e8f5e9;">
    <div class="saldo-label" style="color:#2e7d32;">✓ SERVICIO CANCELADO COMPLETAMENTE</div>
</div>
@endif

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

</body>
</html>
