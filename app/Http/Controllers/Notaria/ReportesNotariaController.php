<?php
namespace App\Http\Controllers\Notaria;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportesNotariaController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());

        $comprobantes = DB::table('comprobantes_sunat')
            ->where('empresa_id', $empresaId)
            ->whereBetween('fecha_emision', [$desde, $hasta])
            ->orderByDesc('fecha_emision')
            ->get();

        return Inertia::render('Notaria/Reportes/Index', compact('comprobantes', 'desde', 'hasta'));
    }
}
