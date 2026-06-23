<?php
namespace App\Http\Controllers\Odontologia;
use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoPaciente;
use App\Models\Odontologia\OdontoCita;
use App\Models\Odontologia\OdontoPresupuesto;
use App\Models\Odontologia\OdontoRadiografia;
use App\Models\Empresa;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Inertia\Inertia;

class PortalPacienteController extends Controller {

    // Generar o regenerar token del paciente
    public function generarToken($id) {
        $paciente = OdontoPaciente::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
        $paciente->update(['portal_token' => Str::random(32)]);
        return response()->json([
            'token' => $paciente->portal_token,
            'url'   => url("/portal/{$paciente->portal_token}")
        ]);
    }

    // Doctores disponibles para el portal
    public function doctores($token) {
        $paciente = OdontoPaciente::where('portal_token', $token)->firstOrFail();
        $doctores = \App\Models\Odontologia\OdontoDoctor::where('empresa_id', $paciente->empresa_id)
            ->where('activo', true)
            ->with('horarios')
            ->get(['id','nombre','especialidad']);
        return response()->json($doctores);
    }

    // Horas disponibles para un doctor en una fecha
    public function horasDisponibles($token) {
        $paciente  = OdontoPaciente::where('portal_token', $token)->firstOrFail();
        $doctorId  = request('doctor_id');
        $fecha     = request('fecha');

        $horarios = \App\Models\Odontologia\OdontoHorarioDoctor::where('doctor_id', $doctorId)
            ->where('dia_semana', \Carbon\Carbon::parse($fecha)->locale('es')->isoFormat('dddd'))
            ->where('activo', true)->get();

        if ($horarios->isEmpty()) return response()->json([]);

        // Citas ya ocupadas ese día
        $ocupadas = \App\Models\Odontologia\OdontoCita::where('doctor_id', $doctorId)
            ->whereDate('fecha_hora', $fecha)
            ->whereNotIn('estado', ['cancelada'])
            ->pluck('fecha_hora')
            ->map(fn($f) => \Carbon\Carbon::parse($f)->format('H:i'))
            ->toArray();

        $horas = [];
        foreach ($horarios as $h) {
            $inicio = \Carbon\Carbon::parse($fecha . ' ' . $h->hora_inicio);
            $fin    = \Carbon\Carbon::parse($fecha . ' ' . $h->hora_fin);
            while ($inicio->copy()->addMinutes(30)->lte($fin)) {
                $hora = $inicio->format('H:i');
                if (!in_array($hora, $ocupadas)) $horas[] = $hora;
                $inicio->addMinutes(30);
            }
        }
        return response()->json($horas);
    }

    // Agendar cita desde el portal
    public function agendarCita($token) {
        $paciente = OdontoPaciente::where('portal_token', $token)->firstOrFail();
        request()->validate([
            'doctor_id'  => 'required|integer',
            'fecha'      => 'required|date|after:today',
            'hora'       => 'required|string',
            'motivo'     => 'nullable|string|max:200',
        ]);

        $fechaHora = request('fecha') . ' ' . request('hora') . ':00';

        \App\Models\Odontologia\OdontoCita::create([
            'empresa_id'  => $paciente->empresa_id,
            'paciente_id' => $paciente->id,
            'doctor_id'   => request('doctor_id'),
            'fecha_hora'  => $fechaHora,
            'duracion_min'=> 30,
            'estado'      => 'pendiente_confirmacion',
            'motivo'      => request('motivo') ?? 'Solicitada por portal',
        ]);

        return response()->json(['ok' => true, 'mensaje' => 'Cita solicitada correctamente. La clínica confirmará pronto.']);
    }

    // Vista pública del portal
    public function show($token) {
        $paciente = OdontoPaciente::where('portal_token', $token)->where('activo', true)->firstOrFail();
        $empresa  = Empresa::find($paciente->empresa_id);

        $citas = OdontoCita::with('doctor')
            ->where('paciente_id', $paciente->id)
            ->orderByDesc('fecha_hora')->limit(10)->get();

        $presupuestos = OdontoPresupuesto::with(['items','doctor'])
            ->where('paciente_id', $paciente->id)
            ->orderByDesc('fecha')->get();

        $radiografias = OdontoRadiografia::where('paciente_id', $paciente->id)
            ->orderByDesc('fecha')->get();

        return Inertia::render('Portal/Paciente', compact(
            'paciente','empresa','citas','presupuestos','radiografias'
        ));
    }
}
