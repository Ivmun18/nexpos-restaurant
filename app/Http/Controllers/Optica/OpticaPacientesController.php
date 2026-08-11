<?php
namespace App\Http\Controllers\Optica;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpticaPaciente;
use App\Models\OpticaHistorial;
use App\Models\OpticaDoctor;
use Inertia\Inertia;

class OpticaPacientesController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $q = $request->get('q');
        $pacientes = OpticaPaciente::where('empresa_id', $empresa_id)
            ->when($q, fn($query) => $query->where(fn($s) =>
                $s->where('nombre','like',"%$q%")
                  ->orWhere('apellidos','like',"%$q%")
                  ->orWhere('dni','like',"%$q%")
            ))
            ->withCount(['fichas','ventas'])
            ->latest()->paginate(20)->withQueryString();
        return Inertia::render('Optica/Pacientes/Index', compact('pacientes','q'));
    }

    public function show($id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $paciente = OpticaPaciente::where('empresa_id',$empresa_id)->findOrFail($id);
        $fichas = $paciente->fichas()->latest()->get();
        $recetas = $paciente->recetas()->latest()->get();
        $ventas = $paciente->ventas()->latest()->take(10)->get();
        $historial = OpticaHistorial::where('empresa_id',$empresa_id)->where('paciente_id',$id)->with('doctor')->latest()->get();
        $doctores = OpticaDoctor::where('empresa_id',$empresa_id)->where('activo',true)->orderBy('nombre')->get(['id','nombre']);
        return Inertia::render('Optica/Pacientes/Show', compact('paciente','fichas','recetas','ventas','historial','doctores'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'=>'required|string|max:100',
            'apellidos'=>'required|string|max:100',
            'dni'=>'nullable|string|max:20',
            'telefono'=>'nullable|string|max:20',
            'email'=>'nullable|email',
            'fecha_nacimiento'=>'nullable|date',
            'sexo'=>'nullable|in:M,F',
            'direccion'=>'nullable|string',
            'observaciones'=>'nullable|string',
        ]);
        $data['empresa_id'] = auth()->user()->empresa_id;
        $paciente = OpticaPaciente::create($data);
        return back()->with('success','Paciente registrado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $paciente = OpticaPaciente::where('empresa_id',$empresa_id)->findOrFail($id);
        $data = $request->validate([
            'nombre'=>'required|string|max:100',
            'apellidos'=>'required|string|max:100',
            'dni'=>'nullable|string|max:20',
            'telefono'=>'nullable|string|max:20',
            'email'=>'nullable|email',
            'fecha_nacimiento'=>'nullable|date',
            'sexo'=>'nullable|in:M,F',
            'direccion'=>'nullable|string',
            'observaciones'=>'nullable|string',
        ]);
        $paciente->update($data);
        return back()->with('success','Paciente actualizado.');
    }

    public function destroy($id)
    {
        $empresa_id = auth()->user()->empresa_id;
        $paciente = OpticaPaciente::where('empresa_id',$empresa_id)->findOrFail($id);
        $paciente->delete();
        return back()->with('success','Paciente eliminado.');
    }
}
