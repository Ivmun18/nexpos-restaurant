<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $tipoDoc }} {{ $serie }}-{{ $numero }}</title>
<style>
    @page { margin: 5mm; size: 80mm auto; }
    body { font-family: 'Courier New', monospace; font-size: 12px; font-weight: bold; color: #000; margin: 0; padding: 0; width: 72mm; }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    .line { border-top: 1px dashed #000; margin: 6px 0; }
    .table { width: 100%; border-collapse: collapse; }
    .table th { text-align: left; font-size: 11px; font-weight: bold; border-bottom: 1px dashed #000; padding: 2px 0; }
    .table td { font-size: 11px; font-weight: bold; padding: 2px 0; word-wrap: break-word; }
    .table { table-layout: fixed; }
    .right { text-align: right; }
    .small { font-size: 10px; font-weight: bold; }
    .mt { margin-top: 8px; }
    .mb { margin-bottom: 8px; }
</style>
</head>
<body>
    <div class="center mb">
        @if($empresa->logo_path && file_exists(storage_path('app/public/' . $empresa->logo_path)))
        <img src="data:image/{{ pathinfo($empresa->logo_path, PATHINFO_EXTENSION) }};base64,{{ base64_encode(file_get_contents(storage_path('app/public/' . $empresa->logo_path))) }}" style="max-width:60mm; max-height:20mm; margin-bottom:4px;" />
        @endif
        <p class="bold" style="font-size:13px; margin:0;">{{ $empresa->razon_social }}</p>
        <p style="margin:2px 0;">RUC: {{ $empresa->ruc }}</p>
    </div>

    <div class="center mb">
        <p class="bold" style="font-size:12px; margin:4px 0;">{{ $tipoDoc }}</p>
        <p class="bold" style="font-size:13px; margin:0;">{{ $serie }} - {{ $numero }}</p>
        <p style="margin:2px 0;">FECHA: {{ $fecha }}</p>
    </div>

    <div class="line"></div>

    <div class="mb">
        <p style="margin:2px 0;">SR(ES) : {{ $clienteNombre }}</p>
        <p style="margin:2px 0;">RUC/DNI : {{ $clienteDocumento }}</p>
        <p style="margin:2px 0;">DIR : {{ $clienteDireccion }}</p>
    </div>

    <div class="line"></div>

    <table class="table">
        <thead>
            <tr>
                <th style="width:8%">#</th>
                <th style="width:42%">PRODUCTO</th>
                <th style="width:12%" class="right">CANT</th>
                <th style="width:18%" class="right">P/U</th>
                <th style="width:20%" class="right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item['descripcion'] }}</td>
                <td class="right">{{ $item['cantidad'] }}</td>
                <td class="right">{{ number_format($item['precio_unitario'], 2) }}</td>
                <td class="right">{{ number_format($item['total'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <div class="right mb">
        <p style="margin:2px 0;">SUBTOTAL S/{{ number_format($subtotal, 2) }}</p>
        @if($igv > 0)
        <p style="margin:2px 0;">IGV 18% S/{{ number_format($igv, 2) }}</p>
        @endif
        <p class="bold" style="font-size:13px; margin:4px 0;">TOTAL S/{{ number_format($total, 2) }}</p>
    </div>

    <div class="mb">
        <p style="margin:2px 0;">SON: {{ $totalLetras }}</p>
        <p style="margin:2px 0;">VENDEDOR: {{ $vendedor }}</p>
    </div>

    <div class="line"></div>

    <div class="mb">
        <p class="bold" style="margin:2px 0;">FORMA DE PAGO: CONTADO</p>
        <p style="margin:2px 0;">METODO DE PAGO:</p>
        <p style="margin:2px 0;">° {{ strtoupper($metodoPago) }} {{ number_format($total, 2) }}</p>
    </div>

    <div class="line"></div>

    @if($exonerada)
    <div class="center small mb">
        <p style="margin:2px 0;">BIENES TRANSFERIDOS EN LA AMAZONÍA REGIÓN</p>
        <p style="margin:2px 0;">SELVA PARA SER CONSUMIDOS EN LA MISMA</p>
    </div>
    <div class="line"></div>
    @endif

    <div class="center small mb">
        <p style="margin:4px 0;">{{ $empresa->direccion }}</p>
        <p style="margin:2px 0;">{{ $empresa->distrito }} - {{ $empresa->provincia }} - {{ $empresa->departamento }}</p>
        <p style="margin:2px 0;">GRACIAS POR SU COMPRA</p>
    </div>

    <div class="center small mt">
        <p style="margin:4px 0;">DESARROLLADO POR:</p>
        <p style="margin:2px 0;">https://nexposolution.com</p>
    </div>
</body>
</html>
