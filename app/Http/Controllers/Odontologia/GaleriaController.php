<?php
namespace App\Http\Controllers\Odontologia;
use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoGaleria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GaleriaController extends Controller {
    private function empresaId() { return auth()->user()->empresa_id; }

    public function index() {
        $galeria = OdontoGaleria::where('empresa_id', $this->empresaId())
            ->orderByDesc('created_at')->get();
        return Inertia::render('Odontologia/Galeria/Index', compact('galeria'));
    }

    public function store(Request $request) {
        $request->validate([
            'foto_antes'   => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
            'foto_despues' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
            'tratamiento'  => 'nullable|string|max:100',
            'titulo'       => 'nullable|string|max:150',
        ]);

        $antes   = $request->file('foto_antes')->store('galeria', 'public');
        $despues = $request->file('foto_despues')->store('galeria', 'public');

        OdontoGaleria::create([
            'empresa_id'   => $this->empresaId(),
            'paciente_id'  => $request->paciente_id ?? null,
            'doctor_id'    => $request->doctor_id ?? null,
            'titulo'       => $request->titulo,
            'tratamiento'  => $request->tratamiento,
            'foto_antes'   => $antes,
            'foto_despues' => $despues,
            'descripcion'  => $request->descripcion,
            'publica'      => $request->boolean('publica'),
        ]);

        return back()->with('success', 'Caso agregado a la galería');
    }

    public function destroy($id) {
        $item = OdontoGaleria::where('empresa_id', $this->empresaId())->findOrFail($id);
        \Illuminate\Support\Facades\Storage::disk('public')->delete([$item->foto_antes, $item->foto_despues]);
        $item->delete();
        return back()->with('success', 'Eliminado');
    }

    public function togglePublica($id) {
        $item = OdontoGaleria::where('empresa_id', $this->empresaId())->findOrFail($id);
        $item->update(['publica' => !$item->publica]);
        return back()->with('success', 'Visibilidad actualizada');
    }
}
