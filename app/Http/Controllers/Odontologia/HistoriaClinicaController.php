<?php
namespace App\Http\Controllers\Odontologia;

use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoHistoriaClinica;
use App\Models\Odontologia\OdontoOdontograma;
use App\Models\Odontologia\OdontoOdontogramaPieza;
use App\Models\Odontologia\OdontoDoctor;
use Illuminate\Http\Request;

class HistoriaClinicaController extends Controller
{
    private function empresaId() { return auth()->user()->empresa->id; }

    public function store(Request $request) {
        $request->validate([
            'paciente_id' => 'required|exists:odonto_pacientes,id',
            'doctor_id'   => 'required|exists:odonto_doctores,id',
            'fecha'       => 'required|date',
        ]);
        OdontoHistoriaClinica::create($request->only([
            'paciente_id','doctor_id','cita_id','fecha','anamnesis','diagnostico','tratamiento_realizado','observaciones'
        ]));
        return back()->with('success', 'Historia clínica registrada.');
    }

    public function guardarOdontograma(Request $request) {
        $request->validate([
            'paciente_id' => 'required|exists:odonto_pacientes,id',
            'doctor_id'   => 'required|exists:odonto_doctores,id',
            'piezas'      => 'required|array',
        ]);

        $odontograma = OdontoOdontograma::create([
            'paciente_id'  => $request->paciente_id,
            'doctor_id'    => $request->doctor_id,
            'fecha'        => now()->toDateString(),
            'observaciones'=> $request->observaciones,
        ]);

        foreach ($request->piezas as $pieza) {
            OdontoOdontogramaPieza::create([
                'odontograma_id' => $odontograma->id,
                'numero_pieza'   => $pieza['numero_pieza'],
                'estado'         => $pieza['estado'] ?? null,
                'cara_mesial'    => $pieza['cara_mesial'] ?? null,
                'cara_distal'    => $pieza['cara_distal'] ?? null,
                'cara_oclusal'   => $pieza['cara_oclusal'] ?? null,
                'cara_vestibular'=> $pieza['cara_vestibular'] ?? null,
                'cara_palatino'  => $pieza['cara_palatino'] ?? null,
                'color'          => $pieza['color'] ?? null,
                'notas'          => $pieza['notas'] ?? null,
            ]);
        }

        return response()->json(['success' => true, 'odontograma_id' => $odontograma->id]);
    }
}
