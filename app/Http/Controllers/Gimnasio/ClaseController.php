<?php
namespace App\Http\Controllers\Gimnasio;
use App\Http\Controllers\Controller;
use App\Models\Gimnasio\GimnasioClase;
use App\Models\Gimnasio\GimnasioHorario;
use App\Models\Gimnasio\GimnasioInstructor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClaseController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;
        $clases = GimnasioClase::where('empresa_id', $empresa_id)
            ->where('activo', true)
            ->withCount('horarios')
            ->orderBy('nombre')
            ->get();
        $instructores = GimnasioInstructor::where('empresa_id', $empresa_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
        $horarios = GimnasioHorario::where('empresa_id', $empresa_id)
            ->where('activo', true)
            ->with(['clase', 'instructor'])
            ->orderByRaw("FIELD(dia,'lunes','martes','miercoles','jueves','viernes','sabado','domingo')")
            ->orderBy('hora_inicio')
            ->get();
        return Inertia::render('Gimnasio/Clases/Index', compact('clases', 'instructores', 'horarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'        => 'required|string|max:255',
            'capacidad_max' => 'required|integer|min:1',
            'duracion_min'  => 'required|integer|min:1',
        ]);
        GimnasioClase::create(array_merge($request->all(), ['empresa_id' => auth()->user()->empresa_id]));
        return back()->with('success', 'Clase creada correctamente.');
    }

    public function update(Request $request, GimnasioClase $clase)
    {
        $clase->update($request->all());
        return back()->with('success', 'Clase actualizada.');
    }

    public function destroy(GimnasioClase $clase)
    {
        $clase->update(['activo' => false]);
        return back()->with('success', 'Clase desactivada.');
    }

    public function storeHorario(Request $request)
    {
        $request->validate([
            'clase_id'      => 'required|exists:gimnasio_clases,id',
            'instructor_id' => 'required|exists:gimnasio_instructores,id',
            'dia'           => 'required|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'hora_inicio'   => 'required',
            'hora_fin'      => 'required',
        ]);
        GimnasioHorario::create(array_merge($request->all(), ['empresa_id' => auth()->user()->empresa_id]));
        return back()->with('success', 'Horario agregado.');
    }

    public function updateHorario(Request $request, GimnasioHorario $horario)
    {
        $horario->update($request->all());
        return back()->with('success', 'Horario actualizado.');
    }

    public function destroyHorario(GimnasioHorario $horario)
    {
        $horario->update(['activo' => false]);
        return back()->with('success', 'Horario eliminado.');
    }
}
