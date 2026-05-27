<?php

namespace App\Http\Controllers\Notaria;

use App\Http\Controllers\Controller;
use App\Models\ActoNotarial;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PortalClienteController extends Controller
{
    public function index()
    {
        return Inertia::render('Notaria/PortalCliente/Index');
    }

    public function consultar(Request $request)
    {
        $request->validate([
            'numero_documento' => 'required|string',
            'numero_expediente' => 'required|string',
        ]);

        // Buscar el cliente por número de documento
        $cliente = Cliente::where('numero_documento', $request->numero_documento)->first();

        if (!$cliente) {
            return back()->withErrors(['numero_documento' => 'No se encontró ningún cliente con ese documento.']);
        }

        // Buscar el acto notarial
        $acto = ActoNotarial::where('numero_expediente', $request->numero_expediente)
            ->where('cliente_id', $cliente->id)
            ->with(['seguimientos' => function($q) {
                $q->orderBy('created_at', 'desc');
            }, 'documentos', 'requisitos', 'usuario'])
            ->first();

        if (!$acto) {
            return back()->withErrors(['numero_expediente' => 'No se encontró el expediente o no pertenece a este cliente.']);
        }

        // Mapear el tipo de acto a texto legible
        $tiposActo = [
            'escritura_publica' => 'Escritura Pública',
            'poder' => 'Poder Notarial',
            'testamento' => 'Testamento',
            'acta' => 'Acta Notarial',
            'protesto' => 'Protesto',
            'compraventa' => 'Compraventa',
            'minuta' => 'Minuta',
        ];

        $acto->tipo_acto_label = $tiposActo[$acto->tipo_acto] ?? ucfirst($acto->tipo_acto);

        // Calcular porcentaje de completitud
        $totalRequisitos = $acto->requisitos->count();
        $requisitosCompletados = $acto->requisitos->where('completado', true)->count();
        $porcentajeCompletitud = $totalRequisitos > 0 ? round(($requisitosCompletados / $totalRequisitos) * 100) : 0;

        return Inertia::render('Notaria/PortalCliente/Show', [
            'acto' => $acto,
            'cliente' => $cliente,
            'porcentaje_completitud' => $porcentajeCompletitud,
        ]);
    }

    public function descargarDocumento($documentoId)
    {
        $documento = \App\Models\ActoDocumento::findOrFail($documentoId);

        // Verificar que el documento esté marcado como público/descargable
        // Aquí puedes agregar lógica adicional de permisos si lo necesitas

        if (!file_exists(storage_path('app/' . $documento->ruta))) {
            abort(404, 'Archivo no encontrado');
        }

        return response()->download(storage_path('app/' . $documento->ruta), $documento->nombre);
    }
}
