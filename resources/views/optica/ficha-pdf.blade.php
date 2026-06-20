<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: Arial, sans-serif; font-size: 11px; padding: 20px; }
  .header { text-align:center; border-bottom: 2px solid #7c3aed; padding-bottom: 10px; margin-bottom: 14px; }
  .header h1 { font-size: 15px; color: #7c3aed; }
  .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px; }
  .campo label { font-size: 9px; color: #888; display: block; }
  .campo span { font-size: 12px; font-weight: bold; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  th { background: #7c3aed; color: white; padding: 5px 8px; font-size: 10px; }
  td { border: 1px solid #ddd; padding: 5px 8px; text-align: center; }
  .od { color: #1d4ed8; font-weight: bold; }
  .oi { color: #15803d; font-weight: bold; }
</style>
</head>
<body>
<div class="header">
  <h1>👁️ FICHA OFTALMOLÓGICA</h1>
</div>
<div class="grid">
  <div class="campo"><label>Paciente</label><span>{{ $ficha->paciente->nombre }} {{ $ficha->paciente->apellidos }}</span></div>
  <div class="campo"><label>DNI</label><span>{{ $ficha->paciente->dni ?? "—" }}</span></div>
  <div class="campo"><label>Fecha</label><span>{{ $ficha->fecha }}</span></div>
  <div class="campo"><label>DIV</label><span>{{ $ficha->div ?? "—" }} mm</span></div>
</div>
<table>
  <thead><tr><th>Ojo</th><th>Esfera</th><th>Cilindro</th><th>Eje</th><th>Adición</th><th>AV</th></tr></thead>
  <tbody>
    <tr>
      <td class="od">OD</td>
      <td>{{ $ficha->od_esfera >= 0 ? "+" : "" }}{{ number_format($ficha->od_esfera,2) }}</td>
      <td>{{ number_format($ficha->od_cilindro,2) }}</td>
      <td>{{ $ficha->od_eje ?? "—" }}°</td>
      <td>{{ $ficha->od_adicion ? "+".number_format($ficha->od_adicion,2) : "—" }}</td>
      <td>{{ $ficha->od_av ?? "—" }}</td>
    </tr>
    <tr>
      <td class="oi">OI</td>
      <td>{{ $ficha->oi_esfera >= 0 ? "+" : "" }}{{ number_format($ficha->oi_esfera,2) }}</td>
      <td>{{ number_format($ficha->oi_cilindro,2) }}</td>
      <td>{{ $ficha->oi_eje ?? "—" }}°</td>
      <td>{{ $ficha->oi_adicion ? "+".number_format($ficha->oi_adicion,2) : "—" }}</td>
      <td>{{ $ficha->oi_av ?? "—" }}</td>
    </tr>
  </tbody>
</table>
@if($ficha->observaciones)
<p><strong>Observaciones:</strong> {{ $ficha->observaciones }}</p>
@endif
</body>
</html>
