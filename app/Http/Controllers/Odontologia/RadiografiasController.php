<?php
namespace App\Http\Controllers\Odontologia;
use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoRadiografia;
use Illuminate\Http\Request;

class RadiografiasController extends Controller {
    private function empresaId() { return auth()->user()->empresa_id; }

    public function store(Request $request) {
        $request->validate([
            'paciente_id' => 'required|integer',
            'tipo'        => 'required|string|max:80',
            'descripcion' => 'nullable|string',
            'archivo'     => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $path = $request->file('archivo')->store('radiografias', 'public');

        OdontoRadiografia::create([
            'paciente_id' => $request->paciente_id,
            'doctor_id'   => $request->doctor_id ?? null,
            'fecha'       => now()->toDateString(),
            'tipo'        => $request->tipo,
            'descripcion' => $request->descripcion,
            'archivo_url' => $path,
        ]);

        return back()->with('success', 'Radiografía guardada');
    }

    public function destroy($id) {
        $r = OdontoRadiografia::findOrFail($id);
        if ($r->archivo_url) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($r->archivo_url);
        }
        $r->delete();
        return back()->with('success', 'Eliminada');
    }
}
