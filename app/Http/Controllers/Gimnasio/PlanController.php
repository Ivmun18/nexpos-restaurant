<?php
namespace App\Http\Controllers\Gimnasio;
use App\Http\Controllers\Controller;
use App\Models\Gimnasio\GimnasioPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;
        $planes = GimnasioPlan::where('empresa_id', $empresa_id)->orderBy('precio')->get();
        return Inertia::render('Gimnasio/Planes/Index', compact('planes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'        => 'required|string|max:255',
            'precio'        => 'required|numeric|min:0',
            'duracion_dias' => 'required|integer|min:1',
        ]);
        GimnasioPlan::create(array_merge($request->all(), ['empresa_id' => auth()->user()->empresa_id]));
        return back()->with('success', 'Plan creado correctamente.');
    }

    public function update(Request $request, GimnasioPlan $plan)
    {
        $plan->update($request->all());
        return back()->with('success', 'Plan actualizado.');
    }

    public function destroy(GimnasioPlan $plan)
    {
        $plan->update(['activo' => false]);
        return back()->with('success', 'Plan desactivado.');
    }
}
