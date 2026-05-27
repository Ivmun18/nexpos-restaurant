<?php

namespace App\Http\Controllers\Notaria;

use App\Http\Controllers\Controller;
use App\Models\IndiceNotarial;
use App\Models\ActoNotarial;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndiceNotarialController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $buscar = $request->get('buscar', '');
        $anio = $request->get('anio', now()->year);
        $tipo = $request->get('tipo', '');
        $cerrado = $request->get('cerrado', '');

        $query = IndiceNotarial::with(['acto', 'usuarioRegistro'])
            ->where('empresa_id', $empresaId)
            ->orderBy('numero_correlativo', 'desc');

        if ($buscar) $query->buscar($buscar);
        if ($anio) $query->porAnio($anio);
        if ($tipo) $query->porTipo($tipo);
        if ($cerrado !== '') $query->where('cerrado', $cerrado === '1');

        $registros = $query->paginate(50);

        // Estadísticas
        $stats = [
            'total_anio' => IndiceNotarial::where('empresa_id', $empresaId)->where('anio', $anio)->count(),
            'total_cerrados' => IndiceNotarial::where('empresa_id', $empresaId)->where('anio', $anio)->where('cerrado', true)->count(),
            'ultimo_numero' => IndiceNotarial::where('empresa_id', $empresaId)->where('anio', $anio)->max('numero_correlativo') ?? 0,
        ];

        // Años disponibles
        $anios = IndiceNotarial::where('empresa_id', $empresaId)
            ->selectRaw('DISTINCT anio')
            ->orderBy('anio', 'desc')
            ->pluck('anio');

        return Inertia::render('Notaria/IndiceNotarial/Index', [
            'registros' => $registros,
            'stats' => $stats,
            'anios' => $anios,
            'filtros' => [
                'buscar' => $buscar,
                'anio' => $anio,
                'tipo' => $tipo,
                'cerrado' => $cerrado,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $actoId = $request->input('acto_id');
        $acto = ActoNotarial::findOrFail($actoId);

        // Verificar que pertenece a la empresa del usuario
        if ($acto->empresa_id !== auth()->user()->empresa_id) {
            return back()->with('error', 'No autorizado');
        }

        // Registrar en el índice
        $registro = IndiceNotarial::registrarActo($acto);

        if (!$registro) {
            return back()->with('error', 'El acto ya está registrado en el índice');
        }

        return back()->with('success', 'Acto registrado en el índice notarial: ' . $registro->numero_indice);
    }

    public function registrarMasivo(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        // Obtener todos los actos finalizados que no están en el índice
        $actos = ActoNotarial::where('empresa_id', $empresaId)
            ->where('estado', 'finalizado')
            ->whereDoesntHave('indiceNotarial')
            ->get();

        $registrados = 0;
        foreach ($actos as $acto) {
            $registro = IndiceNotarial::registrarActo($acto);
            if ($registro) $registrados++;
        }

        return back()->with('success', "Se registraron {$registrados} actos en el índice notarial");
    }

    public function cerrarMes(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer',
            'mes' => 'required|integer|min:1|max:12',
        ]);

        $empresaId = auth()->user()->empresa_id;

        $count = IndiceNotarial::where('empresa_id', $empresaId)
            ->where('anio', $request->anio)
            ->whereMonth('fecha_otorgamiento', $request->mes)
            ->where('cerrado', false)
            ->update(['cerrado' => true]);

        return back()->with('success', "Se cerraron {$count} registros del mes " . $request->mes . '/' . $request->anio);
    }

    public function exportar(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $anio = $request->get('anio', now()->year);

        $registros = IndiceNotarial::where('empresa_id', $empresaId)
            ->where('anio', $anio)
            ->orderBy('numero_correlativo')
            ->get();

        $filename = 'indice-notarial-' . $anio . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($registros) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            fputcsv($file, [
                'N° Índice',
                'N° Correlativo',
                'Año',
                'Fecha Otorgamiento',
                'Tipo Acto',
                'Asunto',
                'Partes',
                'Monto',
                'Usuario Registro',
                'Fecha Registro',
                'Cerrado',
            ]);

            // Datos
            foreach ($registros as $reg) {
                fputcsv($file, [
                    $reg->numero_indice,
                    $reg->numero_correlativo,
                    $reg->anio,
                    $reg->fecha_otorgamiento->format('d/m/Y H:i'),
                    $reg->tipo_acto,
                    $reg->asunto,
                    $reg->partes,
                    'S/ ' . number_format($reg->monto, 2),
                    $reg->usuarioRegistro->name ?? '',
                    $reg->fecha_registro->format('d/m/Y H:i'),
                    $reg->cerrado ? 'Sí' : 'No',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
