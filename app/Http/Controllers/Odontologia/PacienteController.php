<?php
namespace App\Http\Controllers\Odontologia;

use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoPaciente;
use App\Models\Odontologia\OdontoCita;
use App\Models\Odontologia\OdontoHistoriaClinica;
use App\Models\Odontologia\OdontoPresupuesto;
use App\Models\Odontologia\OdontoPago;
use App\Models\Odontologia\OdontoOdontograma;
use App\Models\Odontologia\OdontoDoctor;
use App\Models\Odontologia\OdontoReceta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PacienteController extends Controller
{
    private function empresaId() { return auth()->user()->empresa->id; }

    public function index(Request $request) {
        $empresaId = $this->empresaId();
        $buscar = $request->get('buscar');

        $pacientes = OdontoPaciente::where('empresa_id', $empresaId)
            ->when($buscar, fn($q) => $q->where(function($q) use ($buscar) {
                $q->where('nombres', 'like', "%$buscar%")
                  ->orWhere('apellidos', 'like', "%$buscar%")
                  ->orWhere('dni', 'like', "%$buscar%")
                  ->orWhere('telefono', 'like', "%$buscar%");
            }))
            ->orderBy('apellidos')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Odontologia/Pacientes/Index', compact('pacientes', 'buscar'));
    }

    public function create() {
        return Inertia::render('Odontologia/Pacientes/Create');
    }

    public function store(Request $request) {
        $request->validate([
            'nombres'   => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'dni'       => 'nullable|string|max:20',
            'telefono'  => 'nullable|string|max:20',
            'email'     => 'nullable|email',
        ]);

        OdontoPaciente::create([
            ...$request->only(['nombres','apellidos','dni','fecha_nacimiento','sexo','telefono','email','direccion','grupo_sanguineo','alergias','antecedentes','contacto_emergencia','telefono_emergencia']),
            'empresa_id' => $this->empresaId(),
        ]);

        return redirect()->route('odontologia.pacientes.index')->with('success', 'Paciente registrado correctamente.');
    }

    public function show($id) {
        $empresaId = $this->empresaId();
        $paciente = OdontoPaciente::where('empresa_id', $empresaId)->findOrFail($id);

        $citas = OdontoCita::with('doctor')->where('paciente_id', $id)->orderByDesc('fecha_hora')->limit(10)->get();
        $historias = OdontoHistoriaClinica::with('doctor')->where('paciente_id', $id)->orderByDesc('fecha')->limit(10)->get();
        $presupuestos = OdontoPresupuesto::with(['doctor','items'])->where('paciente_id', $id)->orderByDesc('fecha')->get();
        $pagos = OdontoPago::with('cuotas')->where('paciente_id', $id)->orderByDesc('fecha')->get();

        $odontogramaEventos = OdontoOdontograma::where('empresa_id', $empresaId)
            ->where('paciente_id', $id)
            ->where(function($q) {
                $q->where('estado', '!=', 'sano')->orWhereNotNull('notas');
            })
            ->orderByDesc('updated_at')
            ->get(['diente','estado','notas','updated_at']);

        $doctores = OdontoDoctor::where('empresa_id', $empresaId)->orderBy('nombre')->get(['id','nombre']);
        $recetas = OdontoReceta::with('items')->where('paciente_id', $id)->orderByDesc('fecha')->get();

        return Inertia::render('Odontologia/Pacientes/Show', compact('paciente','citas','historias','presupuestos','pagos','odontogramaEventos','doctores','recetas'));
    }

    public function edit($id) {
        $paciente = OdontoPaciente::where('empresa_id', $this->empresaId())->findOrFail($id);
        return Inertia::render('Odontologia/Pacientes/Edit', compact('paciente'));
    }

    public function update(Request $request, $id) {
        $paciente = OdontoPaciente::where('empresa_id', $this->empresaId())->findOrFail($id);
        $request->validate([
            'nombres'   => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
        ]);
        $paciente->update($request->only(['nombres','apellidos','dni','fecha_nacimiento','sexo','telefono','email','direccion','grupo_sanguineo','alergias','antecedentes','contacto_emergencia','telefono_emergencia']));
        return redirect()->route('odontologia.pacientes.show', $id)->with('success', 'Paciente actualizado.');
    }

    public function buscar(Request $request) {
        $empresaId = $this->empresaId();
        $q = $request->get('q');
        $pacientes = OdontoPaciente::where('empresa_id', $empresaId)
            ->where(function($query) use ($q) {
                $query->where('nombres', 'like', "%$q%")
                      ->orWhere('apellidos', 'like', "%$q%")
                      ->orWhere('dni', 'like', "%$q%");
            })
            ->limit(10)
            ->get(['id','nombres','apellidos','dni','telefono']);
        return response()->json($pacientes);
    }
}
