<?php
namespace App\Http\Controllers\Odontologia;

use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoReceta;
use App\Models\Odontologia\OdontoPaciente;
use Illuminate\Http\Request;

class RecetaController extends Controller
{
    private function empresaId() { return auth()->user()->empresa->id; }

    public function store(Request $request) {
        $request->validate([
            'paciente_id' => 'required|exists:odonto_pacientes,id',
            'doctor_id'   => 'required|exists:odonto_doctores,id',
            'fecha'       => 'required|date',
            'items'       => 'required|array|min:1',
            'items.*.medicamento' => 'required|string',
        ]);

        $receta = OdontoReceta::create($request->only(['paciente_id','doctor_id','cita_id','fecha','indicaciones']));
        foreach ($request->items as $item) {
            $receta->items()->create([
                'medicamento'  => $item['medicamento'] ?? '',
                'dosis'        => $item['dosis'] ?? '',
                'frecuencia'   => $item['frecuencia'] ?? '',
                'duracion'     => $item['duracion'] ?? '',
                'indicaciones' => $item['indicaciones'] ?? '',
            ]);
        }

        return back()->with('success', 'Receta registrada.');
    }

    public function pdf($id) {
        $empresaId = $this->empresaId();
        $receta = OdontoReceta::with(['items','doctor'])->findOrFail($id);
        $paciente = OdontoPaciente::where('empresa_id', $empresaId)->findOrFail($receta->paciente_id);
        $empresa = auth()->user()->empresa;

        $itemsHtml = '';
        foreach ($receta->items as $i => $it) {
            $itemsHtml .= "<tr>
                <td style='padding:8px;border-bottom:1px solid #E2E8F0;font-weight:700;'>".($i+1).". {$it->medicamento}</td>
                <td style='padding:8px;border-bottom:1px solid #E2E8F0;'>{$it->dosis}</td>
                <td style='padding:8px;border-bottom:1px solid #E2E8F0;'>{$it->frecuencia}</td>
                <td style='padding:8px;border-bottom:1px solid #E2E8F0;'>{$it->duracion}</td>
            </tr>";
            if ($it->indicaciones) {
                $itemsHtml .= "<tr><td colspan='4' style='padding:0 8px 8px;color:#64748B;font-size:11px;'>- {$it->indicaciones}</td></tr>";
            }
        }

        $html = "<!DOCTYPE html><html><head><meta charset='utf-8'><style>
            body{font-family:Arial,sans-serif;font-size:12px;color:#1E293B;margin:0;padding:24px;}
            h1{font-size:20px;font-weight:700;color:#8B5CF6;margin:0;}
            .sub{font-size:12px;color:#64748B;margin:2px 0 0;}
            table{width:100%;border-collapse:collapse;margin-top:12px;}
            th{background:#F8FAFC;padding:8px;text-align:left;font-size:11px;color:#64748B;}
            .section-title{font-size:11px;font-weight:700;color:#64748B;text-transform:uppercase;margin:16px 0 6px;border-bottom:1px solid #E2E8F0;padding-bottom:4px;}
            .footer{margin-top:32px;text-align:center;font-size:10px;color:#94A3B8;border-top:1px dashed #E2E8F0;padding-top:10px;}
        </style></head><body>
        <div style='display:flex;justify-content:space-between;align-items:start;'>
            <div><h1>".$empresa->nombre."</h1><div class='sub'>{$empresa->direccion} - {$empresa->telefono}</div></div>
            <div style='text-align:right;'><div style='font-size:16px;font-weight:700;'>RECETA MEDICA</div><div class='sub'>Fecha: {$receta->fecha}</div></div>
        </div>
        <div class='section-title'>Paciente</div>
        <div>{$paciente->apellidos}, {$paciente->nombres} - DNI: {$paciente->dni}</div>
        <div class='section-title'>Medicamentos</div>
        <table><thead><tr><th>Medicamento</th><th>Dosis</th><th>Frecuencia</th><th>Duracion</th></tr></thead><tbody>{$itemsHtml}</tbody></table>
        " . ($receta->indicaciones ? "<div class='section-title'>Indicaciones generales</div><div>{$receta->indicaciones}</div>" : "") . "
        <div class='footer'>Dr(a). " . ($receta->doctor->nombre ?? '') . " - " . $empresa->nombre . " - Generado el " . now()->format('d/m/Y H:i') . "</div>
        </body></html>";

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a5','portrait');
        return $pdf->stream('receta-'.$paciente->apellidos.'.pdf');
    }
}
