<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: Arial, sans-serif; font-size: 10px; padding: 10px; width: 226px; }
  .center { text-align: center; }
  .bold { font-weight: bold; }
  .line { border-top: 1px dashed #999; margin: 6px 0; }
  table { width: 100%; }
  td { padding: 2px 0; vertical-align: top; }
  .total-row td { font-weight: bold; font-size: 12px; border-top: 1px solid #000; padding-top: 4px; }
</style>
</head>
<body>
<div class="center bold" style="font-size:13px;">{{ $empresa->nombre_comercial ?? $empresa->razon_social }}</div>
<div class="center">RUC: {{ $empresa->ruc }}</div>
<div class="center">{{ $empresa->direccion }}</div>
<div class="center">Tel: {{ $empresa->telefono }}</div>
<div class="line"></div>
<div class="center bold" style="font-size:11px;">{{ strtoupper($venta->tipo_comprobante) }}</div>
<div class="center">N°: {{ $venta->numero_venta }}</div>
<div class="center">{{ $venta->fecha }}</div>
<div class="line"></div>
@if($venta->paciente)
<div>Cliente: {{ $venta->paciente->nombre }} {{ $venta->paciente->apellidos }}</div>
@endif
<div class="line"></div>
<table>
  <tr style="border-bottom:1px solid #ccc;">
    <td class="bold">Producto</td><td class="bold" style="text-align:center">Cant</td><td class="bold" style="text-align:right">Precio</td><td class="bold" style="text-align:right">Total</td>
  </tr>
  @foreach($venta->items as $item)
  <tr>
    <td>{{ $item->descripcion }}</td>
    <td style="text-align:center">{{ $item->cantidad }}</td>
    <td style="text-align:right">{{ number_format($item->precio_unitario,2) }}</td>
    <td style="text-align:right">{{ number_format($item->subtotal,2) }}</td>
  </tr>
  @endforeach
  <tr class="total-row">
    <td colspan="3">TOTAL</td>
    <td style="text-align:right">S/ {{ number_format($venta->total,2) }}</td>
  </tr>
</table>
<div class="line"></div>
<div>Pagado: S/ {{ number_format($venta->monto_pagado,2) }}</div>
<div>Vuelto: S/ {{ number_format($venta->vuelto,2) }}</div>
<div>Método: {{ ucfirst($venta->metodo_pago) }}</div>
<div class="line"></div>
<div class="center" style="margin-top:4px;">¡Gracias por su preferencia!</div>
</body>
</html>
