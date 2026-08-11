<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1e293b; }
  .header { background:#1e1b4b; color:#fff; padding:20px 28px; display:flex; justify-content:space-between; align-items:center; }
  .header-logo { font-size:22px; font-weight:700; letter-spacing:1px; }
  .header-sub { font-size:10px; color:#a5b4fc; margin-top:2px; }
  .header-info { text-align:right; font-size:10px; color:#c7d2fe; }
  .paciente-bar { background:#4338ca; color:#fff; padding:12px 28px; display:flex; gap:40px; }
  .pb-item { }
  .pb-label { font-size:9px; color:#c7d2fe; text-transform:uppercase; letter-spacing:.05em; }
  .pb-val { font-size:13px; font-weight:600; margin-top:1px; }
  .body { padding:20px 28px; }
  .section { margin-bottom:18px; }
  .section-title { font-size:10px; font-weight:700; color:#4338ca; text-transform:uppercase; letter-spacing:.08em; border-bottom:2px solid #e0e7ff; padding-bottom:4px; margin-bottom:10px; }
  .info-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
  .info-item { background:#f8fafc; border-radius:5px; padding:7px 10px; }
  .info-label { font-size:9px; color:#94a3b8; text-transform:uppercase; }
  .info-val { font-size:11px; font-weight:500; color:#1e293b; margin-top:2px; }
  table { width:100%; border-collapse:collapse; font-size:10px; }
  th { background:#f1f5f9; padding:6px 8px; text-align:left; color:#64748b; font-weight:600; font-size:9px; text-transform:uppercase; }
  td { padding:6px 8px; border-bottom:1px solid #f1f5f9; color:#374151; }
  .badge { display:inline-block; padding:2px 7px; border-radius:10px; font-size:9px; font-weight:600; }
  .badge-green { background:#dcfce7; color:#166534; }
  .badge-blue { background:#dbeafe; color:#1e40af; }
  .badge-amber { background:#fef3c7; color:#92400e; }
  .badge-purple { background:#f3e8ff; color:#6b21a8; }
  .historia-item { border-left:3px solid #4338ca; padding:8px 12px; margin-bottom:8px; background:#f8fafc; border-radius:0 6px 6px 0; }
  .historia-fecha { font-size:9px; color:#94a3b8; margin-bottom:3px; }
  .historia-nota { font-size:11px; color:#374151; line-height:1.5; }
  .diente { width:28px; height:28px; border:1px solid #c7d2fe; border-radius:3px; background:#fff; text-align:center; font-size:7px; line-height:28px; color:#4338ca; display:inline-block; margin:1px; }
  .diente.caries { background:#fee2e2; border-color:#fca5a5; color:#991b1b; }
  .diente.tratado { background:#dcfce7; border-color:#86efac; color:#166534; }
  .diente.corona { background:#fef3c7; border-color:#fcd34d; color:#92400e; }
  .diente.extraccion { background:#f3f4f6; border-color:#9ca3af; color:#6b7280; text-decoration:line-through; }
  .footer { margin-top:20px; border-top:1px solid #e2e8f0; padding-top:10px; display:flex; justify-content:space-between; color:#94a3b8; font-size:9px; }
  .page-break { page-break-after: always; }
  .firma-box { border:1px solid #e2e8f0; border-radius:6px; padding:12px; text-align:center; width:180px; }
  .firma-line { border-top:1px solid #1e293b; margin:30px 10px 6px; }
</style>
</head>
<body>

<!-- ENCABEZADO -->
<div class="header">
  <div>
    <div class="header-logo">🦷 {{ $empresa->nombre_comercial ?? $empresa->razon_social }}</div>
    <div class="header-sub">{{ $empresa->direccion }} · {{ $empresa->telefono }}</div>
  </div>
  <div class="header-info">
    <div>RUC: {{ $empresa->ruc }}</div>
    <div>Fecha: {{ now()->format('d/m/Y') }}</div>
    <div style="margin-top:4px;font-size:11px;font-weight:600;">FICHA CLÍNICA</div>
  </div>
</div>

<!-- BARRA PACIENTE -->
<div class="paciente-bar">
  <div class="pb-item">
    <div class="pb-label">Paciente</div>
    <div class="pb-val">{{ $paciente->nombres }} {{ $paciente->apellidos }}</div>
  </div>
  <div class="pb-item">
    <div class="pb-label">DNI</div>
    <div class="pb-val">{{ $paciente->dni ?? '—' }}</div>
  </div>
  <div class="pb-item">
    <div class="pb-label">Fecha nac.</div>
    <div class="pb-val">{{ $paciente->fecha_nacimiento ?? '—' }}</div>
  </div>
  <div class="pb-item">
    <div class="pb-label">Grupo sanguíneo</div>
    <div class="pb-val">{{ $paciente->grupo_sanguineo ?? '—' }}</div>
  </div>
  <div class="pb-item">
    <div class="pb-label">Teléfono</div>
    <div class="pb-val">{{ $paciente->telefono ?? '—' }}</div>
  </div>
</div>

<div class="body">

  <!-- DATOS CLÍNICOS -->
  <div class="section">
    <div class="section-title">Datos clínicos</div>
    <div class="info-grid">
      <div class="info-item">
        <div class="info-label">Alergias</div>
        <div class="info-val">{{ $paciente->alergias ?? 'Ninguna conocida' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">Antecedentes</div>
        <div class="info-val">{{ $paciente->antecedentes ?? '—' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">Medicamentos actuales</div>
        <div class="info-val">{{ $paciente->medicamentos ?? '—' }}</div>
      </div>
    </div>
  </div>

  <!-- ODONTOGRAMA -->
  @if($odontograma->count() > 0)
  <div class="section">
    <div class="section-title">Odontograma</div>
    @php
      $piezasSuperiores = [18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28];
      $piezasInferiores = [48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38];
      $estadoMap = $odontograma->keyBy('diente');
    @endphp

    <table style="width:100%;border-collapse:collapse;margin-bottom:4px;">
      <tr>
        @foreach($piezasSuperiores as $num)
          @php $e = $estadoMap[$num]->estado ?? 'sano'; @endphp
          <td style="width:6.25%;padding:1px;text-align:center;">
            <div style="border:1px solid {{ $e==='caries'?'#fca5a5':($e==='tratado'?'#86efac':($e==='corona'?'#fcd34d':'#c7d2fe')) }};
                        background:{{ $e==='caries'?'#fee2e2':($e==='tratado'?'#dcfce7':($e==='corona'?'#fef3c7':'#f8f9ff')) }};
                        color:{{ $e==='caries'?'#991b1b':($e==='tratado'?'#166534':($e==='corona'?'#92400e':'#4338ca')) }};
                        font-size:7px;text-align:center;padding:3px 0;border-radius:3px;">{{ $num }}</div>
          </td>
        @endforeach
      </tr>
      <tr><td colspan="16" style="height:6px;border-bottom:1px dashed #e2e8f0;"></td></tr>
      <tr>
        @foreach($piezasInferiores as $num)
          @php $e = $estadoMap[$num]->estado ?? 'sano'; @endphp
          <td style="width:6.25%;padding:1px;text-align:center;">
            <div style="border:1px solid {{ $e==='caries'?'#fca5a5':($e==='tratado'?'#86efac':($e==='corona'?'#fcd34d':'#c7d2fe')) }};
                        background:{{ $e==='caries'?'#fee2e2':($e==='tratado'?'#dcfce7':($e==='corona'?'#fef3c7':'#f8f9ff')) }};
                        color:{{ $e==='caries'?'#991b1b':($e==='tratado'?'#166534':($e==='corona'?'#92400e':'#4338ca')) }};
                        font-size:7px;text-align:center;padding:3px 0;border-radius:3px;">{{ $num }}</div>
          </td>
        @endforeach
      </tr>
    </table>
    <table style="border-collapse:collapse;font-size:9px;margin-top:4px;">
      <tr>
        <td style="padding:0 8px 0 0;"><span style="background:#fee2e2;padding:1px 6px;border-radius:3px;font-size:8px;">■</span> Caries</td>
        <td style="padding:0 8px 0 0;"><span style="background:#dcfce7;padding:1px 6px;border-radius:3px;font-size:8px;">■</span> Tratado</td>
        <td style="padding:0 8px 0 0;"><span style="background:#fef3c7;padding:1px 6px;border-radius:3px;font-size:8px;">■</span> Corona</td>
        <td><span style="background:#f3f4f6;padding:1px 6px;border-radius:3px;font-size:8px;">■</span> Extracción</td>
      </tr>
    </table>
  </div>
  @endif

  <!-- HISTORIA CLÍNICA -->
  @if($historias->count() > 0)
  <div class="section">
    <div class="section-title">Historia clínica ({{ $historias->count() }} registros)</div>
    @foreach($historias->take(8) as $h)
    <div class="historia-item">
      <div class="historia-fecha">{{ \Carbon\Carbon::parse($h->fecha)->format('d/m/Y') }} · Dr. {{ $h->doctor->nombre ?? '—' }}</div>
      <div class="historia-nota">{{ $h->notas }}</div>
    </div>
    @endforeach
  </div>
  @endif

  <!-- PRESUPUESTOS -->
  @if($presupuestos->count() > 0)
  <div class="section">
    <div class="section-title">Presupuestos</div>
    <table>
      <thead><tr><th>Fecha</th><th>Tratamiento</th><th>Estado</th><th style="text-align:right;">Total S/</th></tr></thead>
      <tbody>
        @foreach($presupuestos as $pre)
        <tr>
          <td>{{ \Carbon\Carbon::parse($pre->fecha)->format('d/m/Y') }}</td>
          <td>{{ $pre->items->pluck('descripcion')->join(', ') }}</td>
          <td><span class="badge badge-{{ $pre->estado==='aprobado'?'green':($pre->estado==='borrador'?'amber':'blue') }}">{{ $pre->estado }}</span></td>
          <td style="text-align:right;font-weight:600;">{{ number_format($pre->total,2) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

  <!-- FIRMA -->
  <div style="display:flex;justify-content:flex-end;margin-top:24px;">
    <div class="firma-box">
      <div class="firma-line"></div>
      <div style="font-size:10px;color:#374151;font-weight:600;">Firma y sello del doctor</div>
      <div style="font-size:9px;color:#94a3b8;margin-top:2px;">COP: _______________</div>
    </div>
  </div>

</div>

<!-- PIE DE PÁGINA -->
<div class="footer" style="padding:0 28px 12px;">
  <div>{{ $empresa->razon_social }} · RUC {{ $empresa->ruc }}</div>
  <div>Generado el {{ now()->format('d/m/Y H:i') }}</div>
</div>

</body>
</html>
