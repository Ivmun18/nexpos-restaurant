<?php

namespace App\Http\Controllers\Notaria;

use App\Http\Controllers\Controller;
use App\Models\ActoNotarial;
use App\Models\ActoParte;
use Illuminate\Http\Request;

class ActoParteController extends Controller
{
    /**
     * Agregar una parte al acto
     */
    public function store(Request $request, ActoNotarial $acto)
    {
        $validated = $request->validate([
            'tipo_persona' => 'required|in:natural,juridica',
            'tipo_documento' => 'required|in:1,4,6,7',
            'numero_documento' => 'required|string|max:20',
            'nombres' => 'required_if:tipo_persona,natural|string|max:255',
            'apellido_paterno' => 'nullable|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'razon_social' => 'required_if:tipo_persona,juridica|string|max:255',
            'rol' => 'required|string|max:50',
            'estado_civil' => 'nullable|in:soltero,casado,viudo,divorciado,conviviente',
            'regimen_patrimonial' => 'nullable|in:sociedad_gananciales,separacion_bienes',
            'nombre_conyuge' => 'nullable|string|max:255',
            'dni_conyuge' => 'nullable|string|max:8',
            'domicilio' => 'nullable|string',
            'distrito' => 'nullable|string|max:100',
            'provincia' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'actua_mediante_representante' => 'boolean',
            'nombre_representante' => 'nullable|string|max:255',
            'dni_representante' => 'nullable|string|max:20',
            'tipo_poder' => 'nullable|string|max:50',
            'facultades_representante' => 'nullable|string',
            'profesion' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'observaciones' => 'nullable|string',
        ]);

        // Calcular el siguiente orden
        $orden = $acto->partes()->max('orden') + 1;
        $validated['orden'] = $orden;

        $parte = $acto->partes()->create($validated);

        return back()->with('success', 'Parte interviniente agregada correctamente');
    }

    /**
     * Actualizar una parte
     */
    public function update(Request $request, ActoParte $parte)
    {
        $validated = $request->validate([
            'tipo_persona' => 'required|in:natural,juridica',
            'tipo_documento' => 'required|in:1,4,6,7',
            'numero_documento' => 'required|string|max:20',
            'nombres' => 'required_if:tipo_persona,natural|string|max:255',
            'apellido_paterno' => 'nullable|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'razon_social' => 'required_if:tipo_persona,juridica|string|max:255',
            'rol' => 'required|string|max:50',
            'estado_civil' => 'nullable|in:soltero,casado,viudo,divorciado,conviviente',
            'regimen_patrimonial' => 'nullable|in:sociedad_gananciales,separacion_bienes',
            'nombre_conyuge' => 'nullable|string|max:255',
            'dni_conyuge' => 'nullable|string|max:8',
            'domicilio' => 'nullable|string',
            'distrito' => 'nullable|string|max:100',
            'provincia' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'actua_mediante_representante' => 'boolean',
            'nombre_representante' => 'nullable|string|max:255',
            'dni_representante' => 'nullable|string|max:20',
            'tipo_poder' => 'nullable|string|max:50',
            'facultades_representante' => 'nullable|string',
            'profesion' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'observaciones' => 'nullable|string',
        ]);

        $parte->update($validated);

        return back()->with('success', 'Parte interviniente actualizada correctamente');
    }

    /**
     * Eliminar una parte
     */
    public function destroy(ActoParte $parte)
    {
        $parte->delete();
        return back()->with('success', 'Parte interviniente eliminada');
    }

    /**
     * Reordenar partes
     */
    public function reordenar(Request $request, ActoNotarial $acto)
    {
        $orden = $request->validate([
            'orden' => 'required|array',
            'orden.*' => 'required|integer|exists:acto_partes,id',
        ])['orden'];

        foreach ($orden as $index => $parteId) {
            ActoParte::where('id', $parteId)->update(['orden' => $index + 1]);
        }

        return back()->with('success', 'Orden actualizado');
    }
}
