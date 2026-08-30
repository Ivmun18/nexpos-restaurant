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

        // El portal es una sola URL compartida por todas las notarías del
        // sistema (sin subdominio por empresa), así que numero_documento por
        // sí solo no identifica una empresa: dos notarías distintas pueden
        // tener cada una un cliente con el mismo DNI. Se exige que el
        // expediente Y el documento del cliente coincidan en el mismo
        // registro para evitar devolver el expediente de la empresa
        // equivocada cuando el DNI se repite entre notarías.
        $acto = ActoNotarial::where('numero_expediente', $request->numero_expediente)
            ->whereHas('cliente', function ($q) use ($request) {
                $q->where('numero_documento', $request->numero_documento);
            })
            ->with(['cliente', 'seguimientos' => function($q) {
                $q->orderBy('created_at', 'desc');
            }, 'documentos', 'requisitos', 'usuario'])
            ->first();

        if (!$acto) {
            return back()->withErrors(['numero_expediente' => 'No se encontró el expediente o no pertenece a este cliente.']);
        }

        $cliente = $acto->cliente;

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

    public function descargarDocumento(Request $request, $documentoId)
    {
        $request->validate([
            'numero_documento' => 'required|string',
        ]);

        $documento = \App\Models\ActoDocumento::with('acto.cliente')->findOrFail($documentoId);

        // Sin esto, cualquiera que adivine/enumere el id del documento
        // (correlativo, fácil de recorrer) podía descargar el archivo de
        // CUALQUIER expediente de CUALQUIER notaría del sistema — el portal
        // es público y no valida identidad más allá de este número.
        if ($documento->acto?->cliente?->numero_documento !== $request->numero_documento) {
            abort(403);
        }

        if (!file_exists(storage_path('app/' . $documento->ruta))) {
            abort(404, 'Archivo no encontrado');
        }

        return response()->download(storage_path('app/' . $documento->ruta), $documento->nombre);
    }
}
