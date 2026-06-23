<?php
namespace App\Http\Controllers\Odontologia;

use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoCita;
use App\Models\Odontologia\OdontoDoctor;
use App\Models\Odontologia\OdontoPaciente;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;

class CitaController extends Controller
{
    private function empresaId() { return auth()->user()->empresa->id; }

    public function index(Request $request) {
        $empresaId = $this->empresaId();
        $fecha = $request->get('fecha', now()->toDateString());
        $doctor_id = $request->get('doctor_id');

        $citas = OdontoCita::with(['paciente','doctor'])
            ->where('empresa_id', $empresaId)
            ->whereDate('fecha_hora', $fecha)
            ->when($doctor_id, fn($q) => $q->where('doctor_id', $doctor_id))
            ->orderBy('fecha_hora')
            ->get();

        $doctores = OdontoDoctor::where('empresa_id', $empresaId)->where('activo', true)->get(['id','nombre']);

        return Inertia::render('Odontologia/Citas/Index', compact('citas','doctores','fecha','doctor_id'));
    }

    public function store(Request $request) {
        $request->validate([
            'paciente_id' => 'required|exists:odonto_pacientes,id',
            'doctor_id'   => 'required|exists:odonto_doctores,id',
            'fecha_hora'  => 'required|date',
            'duracion_min'=> 'required|integer|min:15',
        ]);
        OdontoCita::create([
            ...$request->only(['paciente_id','doctor_id','fecha_hora','duracion_min','motivo','observaciones']),
            'empresa_id' => $this->empresaId(),
            'estado'     => 'programada',
        ]);
        return back()->with('success', 'Cita registrada correctamente.');
    }

    public function update(Request $request, $id) {
        $cita = OdontoCita::where('empresa_id', $this->empresaId())->findOrFail($id);
        $cita->update($request->only(['fecha_hora','duracion_min','estado','motivo','observaciones','doctor_id']));
        return back()->with('success', 'Cita actualizada.');
    }

    public function recordatorio($id) {
        $cita     = OdontoCita::with(['paciente','doctor'])->where('empresa_id', $this->empresaId())->findOrFail($id);
        $paciente = $cita->paciente;
        $telefono = preg_replace('/[^0-9]/', '', $paciente->telefono ?? $paciente->telefono_emergencia ?? '');
        if (!$telefono) return response()->json(['error' => 'Sin teléfono'], 422);
        if (strlen($telefono) === 9) $telefono = '51' . $telefono;

        $empresa = \App\Models\Empresa::find($cita->empresa_id);
        $fecha   = \Carbon\Carbon::parse($cita->fecha_hora)->locale('es')->isoFormat('dddd D [de] MMMM');
        $hora    = \Carbon\Carbon::parse($cita->fecha_hora)->format('H:i');
        $doctor  = $cita->doctor->nombre ?? 'su doctor';
        $clinica = $empresa->nombre_comercial ?? $empresa->razon_social;

        $mensaje = "Hola {$paciente->nombres} 👋\n\n"
            . "Le recordamos su *cita dental*:\n"
            . "📅 *{$fecha}* a las *{$hora}*\n"
            . "👨‍⚕️ Dr. {$doctor}\n"
            . "🦷 {$clinica}\n\n"
            . "Para confirmar o reprogramar llámenos al {$empresa->telefono}.\n"
            . "_Por favor llegue 5 minutos antes._";

        $url = "https://wa.me/{$telefono}?text=" . urlencode($mensaje);
        return response()->json(['url' => $url]);
    }

    public function destroy($id) {
        $cita = OdontoCita::where('empresa_id', $this->empresaId())->findOrFail($id);
        $cita->update(['estado' => 'cancelada']);
        return back()->with('success', 'Cita cancelada.');
    }
}
