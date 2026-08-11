<?php
namespace App\Http\Controllers\Odontologia;
use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoOdontograma;
use App\Models\Odontologia\OdontoPaciente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OdontogramaController extends Controller {
    private function empresaId() { return auth()->user()->empresa->id; }

    public function show($pacienteId) {
        $empresaId = $this->empresaId();
        $paciente = OdontoPaciente::where('empresa_id', $empresaId)->findOrFail($pacienteId);
        $odontograma = OdontoOdontograma::where('empresa_id', $empresaId)
            ->where('paciente_id', $pacienteId)
            ->get(['diente','estado','notas'])
            ->keyBy('diente');
        return Inertia::render('Odontologia/Odontograma/Index', compact('paciente','odontograma'));
    }

    public function update(Request $request, $pacienteId) {
        $empresaId = $this->empresaId();
        $request->validate([
            'diente' => 'required|integer',
            'estado' => 'required|in:sano,caries,tratamiento,extraccion,ausente,corona,implante,sellante',
            'notas'  => 'nullable|string',
        ]);
        OdontoOdontograma::updateOrCreate(
            ['empresa_id' => $empresaId, 'paciente_id' => $pacienteId, 'diente' => $request->diente],
            ['estado' => $request->estado, 'notas' => $request->notas]
        );
        return response()->json(['ok' => true]);
    }

    public function pdf($pacienteId) {
        $empresaId = $this->empresaId();
        $paciente = OdontoPaciente::where('empresa_id', $empresaId)->findOrFail($pacienteId);
        $empresa = auth()->user()->empresa;
        $dientes = \DB::table('odonto_odontograma')
            ->where('empresa_id', $empresaId)
            ->where('paciente_id', $pacienteId)
            ->get()->keyBy('diente');

        $estados = [
            'sano'        => ['label'=>'Sano',        'color'=>'#0F6E56'],
            'caries'      => ['label'=>'Caries',      'color'=>'#A32D2D'],
            'tratamiento' => ['label'=>'Tratamiento',  'color'=>'#854F0B'],
            'extraccion'  => ['label'=>'Extracción',   'color'=>'#534AB7'],
            'ausente'     => ['label'=>'Ausente',      'color'=>'#888780'],
            'corona'      => ['label'=>'Corona',       'color'=>'#185FA5'],
            'implante'    => ['label'=>'Implante',     'color'=>'#15803D'],
            'sellante'    => ['label'=>'Sellante',     'color'=>'#9333EA'],
        ];

        $fills = [
            'sano'=>'#9FE1CB','caries'=>'#F09595','tratamiento'=>'#FAC775',
            'extraccion'=>'#AFA9EC','ausente'=>'#B4B2A9','corona'=>'#85B7EB',
            'implante'=>'#97C459','sellante'=>'#ED93B1',
        ];

        $supFDI = [18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28];
        $infFDI = [48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38];

        $makeCells = function($fdis) use ($dientes, $fills, $estados) {
            $cells = '';
            foreach ($fdis as $fdi) {
                $d = $dientes->get($fdi);
                $estado = $d ? $d->estado : 'sano';
                $fill = $fills[$estado] ?? '#9FE1CB';
                $color = $estados[$estado]['color'] ?? '#0F6E56';
                $nota = $d && $d->notas ? '<br><small style="color:#666;">' . $d->notas . '</small>' : '';
                $cells .= "<td style='width:30px;height:40px;text-align:center;vertical-align:middle;border:1px solid #E2E8F0;border-radius:6px;background:{$fill};color:{$color};font-weight:700;font-size:10px;padding:2px;'>{$fdi}{$nota}</td>";
            }
            return $cells;
        };

        $notasList = '';
        foreach ($dientes as $fdi => $d) {
            if ($d->estado !== 'sano' || $d->notas) {
                $label = $estados[$d->estado]['label'] ?? $d->estado;
                $color = $estados[$d->estado]['color'] ?? '#333';
                $notasList .= "<tr><td style='padding:5px 8px;font-weight:700;color:{$color};'>{$fdi}</td><td style='padding:5px 8px;'>{$label}</td><td style='padding:5px 8px;color:#64748B;'>" . ($d->notas ?: '-') . "</td></tr>";
            }
        }

        $html = "<!DOCTYPE html><html><head><meta charset='utf-8'>
        <style>
            body{font-family:Arial,sans-serif;font-size:12px;color:#1E293B;margin:0;padding:24px;}
            h1{font-size:20px;font-weight:700;color:#8B5CF6;margin:0;}
            .sub{font-size:12px;color:#64748B;margin:2px 0 0;}
            .section{margin:16px 0;}
            .section-title{font-size:11px;font-weight:700;color:#64748B;text-transform:uppercase;margin:0 0 6px;border-bottom:1px solid #E2E8F0;padding-bottom:4px;}
            table.info{width:100%;}
            table.info td{padding:4px 0;font-size:12px;}
            table.info .label{color:#94A3B8;width:120px;}
            table.odonto{border-collapse:separate;border-spacing:2px;width:100%;}
            table.detail{width:100%;border-collapse:collapse;}
            table.detail th{background:#F8FAFC;padding:6px 8px;text-align:left;font-size:11px;color:#64748B;}
            table.detail td{border-bottom:1px solid #F1F5F9;}
            .legend{display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;}
            .leg-item{display:flex;align-items:center;gap:4px;font-size:10px;}
            .leg-dot{width:10px;height:10px;border-radius:2px;display:inline-block;}
            .footer{margin-top:24px;text-align:center;font-size:10px;color:#94A3B8;border-top:1px dashed #E2E8F0;padding-top:10px;}
        </style></head><body>
        <div style='display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;'>
            <div>
                <h1>🦷 {$empresa->nombre}</h1>
                <div class='sub'>{$empresa->direccion} · {$empresa->telefono}</div>
            </div>
            <div style='text-align:right;'>
                <div style='font-size:16px;font-weight:700;'>FICHA ODONTOLÓGICA</div>
                <div style='font-size:11px;color:#64748B;'>Fecha: " . now()->format('d/m/Y') . "</div>
            </div>
        </div>
        <div class='section'>
            <div class='section-title'>Datos del paciente</div>
            <table class='info'>
                <tr><td class='label'>Nombre completo</td><td><strong>{$paciente->apellidos}, {$paciente->nombres}</strong></td><td class='label'>DNI</td><td>{$paciente->dni}</td></tr>
                <tr><td class='label'>Teléfono</td><td>{$paciente->telefono}</td><td class='label'>Email</td><td>{$paciente->email}</td></tr>
                <tr><td class='label'>Fecha nacimiento</td><td>{$paciente->fecha_nacimiento}</td><td class='label'>Grupo sanguíneo</td><td>{$paciente->grupo_sanguineo}</td></tr>
                " . ($paciente->alergias ? "<tr><td class='label'>Alergias</td><td colspan='3' style='color:#A32D2D;font-weight:600;'>{$paciente->alergias}</td></tr>" : "") . "
            </table>
        </div>
        <div class='section'>
            <div class='section-title'>Odontograma</div>
            <table class='odonto'>
                <tr>{$makeCells($supFDI)}</tr>
                <tr style='height:8px;'></tr>
                <tr>{$makeCells($infFDI)}</tr>
            </table>
            <div class='legend'>
                <div class='leg-item'><span class='leg-dot' style='background:#9FE1CB;border:1px solid #0F6E56;'></span>Sano</div>
                <div class='leg-item'><span class='leg-dot' style='background:#F09595;border:1px solid #A32D2D;'></span>Caries</div>
                <div class='leg-item'><span class='leg-dot' style='background:#FAC775;border:1px solid #854F0B;'></span>Tratamiento</div>
                <div class='leg-item'><span class='leg-dot' style='background:#AFA9EC;border:1px solid #534AB7;'></span>Extracción</div>
                <div class='leg-item'><span class='leg-dot' style='background:#B4B2A9;border:1px solid #888780;'></span>Ausente</div>
                <div class='leg-item'><span class='leg-dot' style='background:#85B7EB;border:1px solid #185FA5;'></span>Corona</div>
                <div class='leg-item'><span class='leg-dot' style='background:#97C459;border:1px solid #15803D;'></span>Implante</div>
                <div class='leg-item'><span class='leg-dot' style='background:#ED93B1;border:1px solid #9333EA;'></span>Sellante</div>
            </div>
        </div>
        " . ($notasList ? "
        <div class='section'>
            <div class='section-title'>Detalle de tratamientos</div>
            <table class='detail'>
                <thead><tr><th>Diente</th><th>Estado</th><th>Observaciones</th></tr></thead>
                <tbody>{$notasList}</tbody>
            </table>
        </div>" : "") . "
        <div class='footer'>{$empresa->nombre} · Ficha generada el " . now()->format('d/m/Y H:i') . "</div>
        </body></html>";

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4','portrait');
        return $pdf->stream('odontograma-'.$paciente->apellidos.'.pdf');
    }

}
