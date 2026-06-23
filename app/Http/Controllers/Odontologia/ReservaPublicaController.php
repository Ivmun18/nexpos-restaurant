<?php
namespace App\Http\Controllers\Odontologia;
use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Odontologia\OdontoDoctor;
use App\Models\Odontologia\OdontoHorarioDoctor;
use App\Models\Odontologia\OdontoCita;
use App\Models\Odontologia\OdontoPaciente;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;

class ReservaPublicaController extends Controller {

    public function show($slug) {
        $empresa = Empresa::where('industry_type', 'odontologia')
            ->where(function($q) use ($slug) {
                $q->whereRaw("LOWER(REPLACE(nombre_comercial, ' ', '-')) = ?", [strtolower($slug)])
                  ->orWhereRaw("LOWER(REPLACE(razon_social, ' ', '-')) = ?", [strtolower($slug)])
                  ->orWhere('id', $slug);
            })->firstOrFail();

        $doctores = OdontoDoctor::where('empresa_id', $empresa->id)
            ->where('activo', true)->get(['id','nombre','especialidad']);

        return Inertia::render('Portal/Reserva', compact('empresa','doctores'));
    }

    public function horas(Request $request, $slug) {
        $empresa  = Empresa::findOrFail($slug);
        $doctorId = $request->doctor_id;
        $fecha    = $request->fecha;

        $diaSemana = Carbon::parse($fecha)->locale('es')->isoFormat('dddd');

        $horarios = OdontoHorarioDoctor::where('doctor_id', $doctorId)
            ->where('dia_semana', $diaSemana)->where('activo', true)->get();

        if ($horarios->isEmpty()) return response()->json([]);

        $ocupadas = OdontoCita::where('doctor_id', $doctorId)
            ->whereDate('fecha_hora', $fecha)
            ->whereNotIn('estado', ['cancelada'])
            ->pluck('fecha_hora')
            ->map(fn($f) => Carbon::parse($f)->format('H:i'))->toArray();

        $horas = [];
        foreach ($horarios as $h) {
            $inicio = Carbon::parse($fecha . ' ' . $h->hora_inicio);
            $fin    = Carbon::parse($fecha . ' ' . $h->hora_fin);
            while ($inicio->copy()->addMinutes(30)->lte($fin)) {
                $hora = $inicio->format('H:i');
                if (!in_array($hora, $ocupadas)) $horas[] = $hora;
                $inicio->addMinutes(30);
            }
        }
        return response()->json($horas);
    }

    public function agendar(Request $request, $slug) {
        $empresa = Empresa::findOrFail($slug);

        $request->validate([
            'nombres'   => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'telefono'  => 'required|string|max:20',
            'doctor_id' => 'required|integer',
            'fecha'     => 'required|date|after:today',
            'hora'      => 'required|string',
        ]);

        // Buscar o crear paciente
        $paciente = OdontoPaciente::firstOrCreate(
            ['empresa_id' => $empresa->id, 'telefono' => $request->telefono],
            ['nombres' => $request->nombres, 'apellidos' => $request->apellidos, 'activo' => true]
        );

        OdontoCita::create([
            'empresa_id'   => $empresa->id,
            'paciente_id'  => $paciente->id,
            'doctor_id'    => $request->doctor_id,
            'fecha_hora'   => $request->fecha . ' ' . $request->hora . ':00',
            'duracion_min' => 30,
            'estado'       => 'pendiente_confirmacion',
            'motivo'       => $request->motivo ?? 'Solicitada por portal público',
        ]);

        return response()->json(['ok' => true, 'mensaje' => 'Cita solicitada. La clínica le confirmará pronto.']);
    }
}
