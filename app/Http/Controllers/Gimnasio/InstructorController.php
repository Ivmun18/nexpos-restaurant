<?php
namespace App\Http\Controllers\Gimnasio;
use App\Http\Controllers\Controller;
use App\Models\Gimnasio\GimnasioInstructor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstructorController extends Controller
{
    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;
        $instructores = GimnasioInstructor::where('empresa_id', $empresa_id)->where('activo', true)->orderBy('nombre')->get();
        return Inertia::render('Gimnasio/Instructores/Index', compact('instructores'));
    }

    public function store(Request $request)
    {
        $request->validate(['nombre' => 'required', 'apellidos' => 'required']);
        GimnasioInstructor::create(array_merge($request->all(), ['empresa_id' => auth()->user()->empresa_id]));
        return back()->with('success', 'Instructor registrado.');
    }

    public function update(Request $request, GimnasioInstructor $instructor)
    {
        $instructor->update($request->all());
        return back()->with('success', 'Instructor actualizado.');
    }

    public function destroy(GimnasioInstructor $instructor)
    {
        $instructor->update(['activo' => false]);
        return back()->with('success', 'Instructor desactivado.');
    }
}
