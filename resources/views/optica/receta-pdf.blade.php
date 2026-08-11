<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: Arial, sans-serif; font-size: 11px; color: #222; padding: 20px; }
  .header { text-align:center; border-bottom: 2px solid #1e40af; padding-bottom: 10px; margin-bottom: 14px; }
  .header h1 { font-size: 16px; color: #1e40af; }
  .header p { font-size: 10px; color: #555; }
  .receta-num { font-size: 13px; font-weight: bold; color: #1e40af; text-align: right; margin-bottom: 10px; }
  .paciente { background: #f0f4ff; border-radius: 6px; padding: 10px; margin-bottom: 14px; }
  .paciente h3 { font-size: 11px; color: #555; margin-bottom: 4px; }
  .paciente .nombre { font-size: 14px; font-weight: bold; }
  .grad-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
  .grad-table th { background: #1e40af; color: white; padding: 6px 8px; font-size: 10px; }
  .grad-table td { border: 1px solid #ddd; padding: 6px 8px; text-align: center; font-size: 11px; }
  .grad-table .ojo { font-weight: bold; background: #f8f9fa; }
  .od { color: #1d4ed8; } .oi { color: #15803d; }
  .indicaciones { background: #fffbeb; border: 1px solid #fcd34d; border-radius: 6px; padding: 10px; margin-bottom: 14px; }
  .footer { text-align: center; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; }
  .firma { margin-top: 30px; text-align: center; }
  .firma-linea { border-top: 1px solid #333; width: 160px; margin: 0 auto 4px; }
</style>
</head>
<body>
<div class="header">
  <h1>👁️ RECETA OFTALMOLÓGICA</h1>
  <p>Óptica VisionPlus — Huánuco, Perú</p>
</div>
<div class="receta-num">{{ $receta->numero_receta }} &nbsp;|&nbsp; {{ $receta->fecha }}</div>
<div class="paciente">
  <h3>PACIENTE</h3>
  <div class="nombre">{{ $receta->paciente->nombre }} {{ $receta->paciente->apellidos }}</div>
  <div>DNI: {{ $receta->paciente->dni ?? "—" }} &nbsp;|&nbsp; Tel: {{ $receta->paciente->telefono ?? "—" }}</div>
</div>

@if($receta->ficha)
<table class="grad-table">
  <thead>
    <tr>
      <th>Ojo</th><th>Esfera</th><th>Cilindro</th><th>Eje</th><th>Adición</th><th>AV</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td class="ojo od">OD</td>
      <td>{{ $receta->ficha->od_esfera >= 0 ? "+" : "" }}{{ number_format($receta->ficha->od_esfera, 2) }}</td>
      <td>{{ number_format($receta->ficha->od_cilindro, 2) }}</td>
      <td>{{ $receta->ficha->od_eje ?? "—" }}°</td>
      <td>{{ $receta->ficha->od_adicion ? "+".number_format($receta->ficha->od_adicion,2) : "—" }}</td>
      <td>{{ $receta->ficha->od_av ?? "—" }}</td>
    </tr>
    <tr>
      <td class="ojo oi">OI</td>
      <td>{{ $receta->ficha->oi_esfera >= 0 ? "+" : "" }}{{ number_format($receta->ficha->oi_esfera, 2) }}</td>
      <td>{{ number_format($receta->ficha->oi_cilindro, 2) }}</td>
      <td>{{ $receta->ficha->oi_eje ?? "—" }}°</td>
      <td>{{ $receta->ficha->oi_adicion ? "+".number_format($receta->ficha->oi_adicion,2) : "—" }}</td>
      <td>{{ $receta->ficha->oi_av ?? "—" }}</td>
    </tr>
  </tbody>
</table>
@if($receta->ficha->div)
<p style="margin-bottom:10px;"><strong>DIV:</strong> {{ $receta->ficha->div }} mm</p>
@endif
@endif

<div class="indicaciones">
  <strong>Indicaciones:</strong> {{ $receta->indicaciones ?? "Uso permanente." }}
</div>
<div class="firma">
  <div class="firma-linea"></div>
  <div style="font-size:10px; color:#555;">Optometrista / Responsable</div>
</div>
<div class="footer" style="font-size:9px; color:#888;">
  Tipo: {{ ucfirst($receta->tipo) }} &nbsp;|&nbsp; Emitida el {{ $receta->created_at->format("d/m/Y H:i") }}
</div>
</body>
</html>
