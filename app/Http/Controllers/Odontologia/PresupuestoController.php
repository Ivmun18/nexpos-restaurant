<?php
namespace App\Http\Controllers\Odontologia;

use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoPresupuesto;
use App\Models\Odontologia\OdontoPresupuestoItem;
use App\Models\Odontologia\OdontoTratamientoCatalogo;
use App\Models\Odontologia\OdontoDoctor;
use App\Models\Odontologia\OdontoPaciente;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class PresupuestoController extends Controller
{
    private function empresaId() { return auth()->user()->empresa->id; }

    public function index(Request $request) {
        $empresaId = $this->empresaId();
        $presupuestos = OdontoPresupuesto::with(['paciente','doctor','items'])
            ->where('empresa_id', $empresaId)
            ->orderByDesc('fecha')
            ->paginate(20);
        $doctores = OdontoDoctor::where('empresa_id', $empresaId)->where('activo', true)->get(['id','nombre']);
        $pacientes = OdontoPaciente::where('empresa_id', $empresaId)->where('activo', true)->get(['id','nombres','apellidos','dni']);
        return Inertia::render('Odontologia/Presupuestos/Index', compact('presupuestos','doctores','pacientes'));
    }

    public function store(Request $request) {
        $request->validate([
            'paciente_id' => 'required|exists:odonto_pacientes,id',
            'doctor_id'   => 'required|exists:odonto_doctores,id',
            'items'       => 'required|array|min:1',
            'items.*.descripcion' => 'required|string',
            'items.*.precio'      => 'required|numeric|min:0',
            'items.*.cantidad'    => 'required|integer|min:1',
        ]);

        $total = collect($request->items)->sum(fn($i) => $i['precio'] * $i['cantidad']);

        $presupuesto = OdontoPresupuesto::create([
            'empresa_id'   => $this->empresaId(),
            'paciente_id'  => $request->paciente_id,
            'doctor_id'    => $request->doctor_id,
            'fecha'        => now()->toDateString(),
            'estado'       => 'borrador',
            'total'        => $total,
            'observaciones'=> $request->observaciones,
        ]);

        foreach ($request->items as $item) {
            OdontoPresupuestoItem::create([
                'presupuesto_id' => $presupuesto->id,
                'tratamiento_id' => $item['tratamiento_id'] ?? null,
                'numero_pieza'   => $item['numero_pieza'] ?? null,
                'descripcion'    => $item['descripcion'],
                'precio'         => $item['precio'],
                'cantidad'       => $item['cantidad'],
                'subtotal'       => $item['precio'] * $item['cantidad'],
                'estado_item'    => 'pendiente',
            ]);
        }

        return back()->with('success', 'Presupuesto creado correctamente.');
    }

    public function update(Request $request, $id) {
        $presupuesto = OdontoPresupuesto::where('empresa_id', $this->empresaId())->findOrFail($id);
        $presupuesto->update($request->only(['estado','observaciones']));
        return back()->with('success', 'Presupuesto actualizado.');
    }

    public function catalogo() {
        $tratamientos = OdontoTratamientoCatalogo::where('empresa_id', $this->empresaId())
            ->where('activo', true)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();
        return response()->json($tratamientos);
    }

    public function storeCatalogo(Request $request) {
        $request->validate([
            'nombre'     => 'required|string|max:100',
            'precio_base'=> 'required|numeric|min:0',
        ]);
        OdontoTratamientoCatalogo::create([
            ...$request->only(['nombre','categoria','descripcion','precio_base']),
            'empresa_id' => $this->empresaId(),
        ]);
        return back()->with('success', 'Tratamiento agregado al catálogo.');
    }

    public function pdf($id) {
        $empresaId = $this->empresaId();
        $p = OdontoPresupuesto::with(['paciente','doctor','items'])
            ->where('empresa_id', $empresaId)
            ->findOrFail($id);
        $empresa = auth()->user()->empresa;
        $pdf = Pdf::loadHTML($this->htmlPresupuesto($p, $empresa))->setPaper('a4','portrait');
        return $pdf->stream('presupuesto-'.$p->id.'.pdf');
    }

    private function htmlPresupuesto($p, $empresa) {
        $items = $p->items->map(fn($i) => "
            <tr>
                <td style='padding:8px 12px; border-bottom:1px solid #F1F5F9;'>{$i->descripcion}</td>
                <td style='padding:8px 12px; border-bottom:1px solid #F1F5F9; text-align:center;'>{$i->cantidad}</td>
                <td style='padding:8px 12px; border-bottom:1px solid #F1F5F9; text-align:right;'>S/ ".number_format($i->precio,2)."</td>
                <td style='padding:8px 12px; border-bottom:1px solid #F1F5F9; text-align:right; font-weight:600;'>S/ ".number_format($i->precio * $i->cantidad,2)."</td>
            </tr>")->implode('');

        return "<!DOCTYPE html><html><head><meta charset='utf-8'>
        <style>
            body { font-family: Arial, sans-serif; font-size: 13px; color: #1E293B; margin: 0; padding: 32px; }
            .header { display: flex; justify-content: space-between; margin-bottom: 32px; }
            .empresa { font-size: 20px; font-weight: 700; color: #8B5CF6; }
            .badge { background: #F5F3FF; color: #8B5CF6; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
            table { width: 100%; border-collapse: collapse; }
            th { background: #F8FAFC; padding: 10px 12px; text-align: left; font-size: 12px; color: #64748B; font-weight: 600; }
            .total-row td { padding: 12px; font-size: 16px; font-weight: 700; color: #8B5CF6; text-align: right; border-top: 2px solid #E2E8F0; }
        </style></head><body>
        <div class='header'>
            <div>
                <div class='empresa'>🦷 {$empresa->nombre}</div>
                <div style='color:#64748B; margin-top:4px;'>{$empresa->direccion}</div>
                <div style='color:#64748B;'>{$empresa->telefono}</div>
            </div>
            <div style='text-align:right;'>
                <div style='font-size:22px; font-weight:700;'>PRESUPUESTO #{$p->id}</div>
                <div style='color:#64748B; margin-top:4px;'>Fecha: {$p->fecha}</div>
                <span class='badge'>".strtoupper($p->estado)."</span>
            </div>
        </div>
        <div style='background:#F8FAFC; border-radius:8px; padding:16px; margin-bottom:24px; display:flex; gap:40px;'>
            <div><div style='font-size:11px; color:#94A3B8; font-weight:600;'>PACIENTE</div>
                 <div style='font-weight:700; margin-top:2px;'>{$p->paciente->apellidos}, {$p->paciente->nombres}</div>
                 <div style='color:#64748B;'>DNI: {$p->paciente->dni}</div></div>
            <div><div style='font-size:11px; color:#94A3B8; font-weight:600;'>DOCTOR</div>
                 <div style='font-weight:700; margin-top:2px;'>{$p->doctor->nombre}</div></div>
        </div>
        <table>
            <thead><tr>
                <th>Tratamiento</th><th style='text-align:center;'>Cant.</th>
                <th style='text-align:right;'>Precio Unit.</th><th style='text-align:right;'>Subtotal</th>
            </tr></thead>
            <tbody>{$items}</tbody>
            <tfoot><tr class='total-row'><td colspan='3'>TOTAL</td><td>S/ ".number_format($p->total,2)."</td></tr></tfoot>
        </table>
        ".($p->observaciones ? "<div style='margin-top:24px; background:#F8FAFC; border-radius:8px; padding:14px;'><div style='font-size:11px; color:#94A3B8; font-weight:600;'>OBSERVACIONES</div><div style='margin-top:4px;'>{$p->observaciones}</div></div>" : "")."
        <div style='margin-top:40px; text-align:center; color:#94A3B8; font-size:11px;'>
            Este presupuesto tiene validez de 30 días desde la fecha de emisión.
        </div>
        </body></html>";
    }

}
