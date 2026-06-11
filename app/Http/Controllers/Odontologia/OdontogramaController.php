<?php
namespace App\Http\Controllers\Odontologia;
use App\Http\Controllers\Controller;
use App\Models\Odontologia\OdontoOdontograma;
use App\Models\Odontologia\OdontoPaciente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OdontogramaController extends Controller {
    private function empresaId() { return auth()->user()->empresa->id; }

    public function show($pacienteId) {
        $empresaId = $this->empresaId();
        $paciente = OdontoPaciente::where('empresa_id', $empresaId)->findOrFail($pacienteId);
        $odontograma = OdontoOdontograma::where('empresa_id', $empresaId)
            ->where('paciente_id', $pacienteId)
            ->get(['diente','estado','notas'])
            ->keyBy('diente');
        return Inertia::render('Odontologia/Odontograma/Index', compact('paciente','odontograma'));
    }

    public function update(Request $request, $pacienteId) {
        $empresaId = $this->empresaId();
        $request->validate([
            'diente' => 'required|integer',
            'estado' => 'required|in:sano,caries,tratamiento,extraccion,ausente,corona,implante,sellante',
            'notas'  => 'nullable|string',
        ]);
        OdontoOdontograma::updateOrCreate(
            ['empresa_id' => $empresaId, 'paciente_id' => $pacienteId, 'diente' => $request->diente],
            ['estado' => $request->estado, 'notas' => $request->notas]
        );
        return response()->json(['ok' => true]);
    }
}
