<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Venta\VentaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\Admin\ProveedorController;
use App\Http\Controllers\Caja\CajaController;
use App\Http\Controllers\Venta\NotaCreditoController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Compra\CompraController;
use App\Http\Controllers\Api\ConsultaController;
use App\Http\Controllers\Venta\CotizacionController;
use App\Http\Controllers\Mesa\MesaController;
use App\Http\Controllers\MenuCategoriaController;
use App\Http\Controllers\MenuProductoController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\CocinaController;
use App\Http\Controllers\CajaRestauranteController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\Admin\MozoController;


Route::get('/', fn() => redirect('/login'));

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::middleware('auth')->group(function () {

Route::get('/dashboard', function () {
    $rol = auth()->user()->rol ?? 'admin';
    $industryType = auth()->user()->empresa->industry_type ?? 'restaurante';
    $industryName = ucfirst($industryType);
    if ($industryType === 'restaurante') {
        if ($rol === 'cocina') return redirect('/cocina');
        if ($rol === 'cajero') return redirect('/mesas');
        if ($rol === 'mozo')   return redirect('/mesas');
    }


    if ($industryType === 'ferreteria') {
        $hoy  = now()->toDateString();
        $mes  = now()->month;
        $anio = now()->year;
        $empresaId = auth()->user()->empresa_id;

        $ventasHoy      = \App\Models\Venta::where('empresa_id', $empresaId)->whereDate('fecha_emision', $hoy)->where('estado','!=','anulado')->sum('total');
        $ventasMes      = \App\Models\Venta::where('empresa_id', $empresaId)->whereMonth('fecha_emision', $mes)->whereYear('fecha_emision', $anio)->where('estado','!=','anulado')->sum('total');
        $ventasHoyCount = \App\Models\Venta::where('empresa_id', $empresaId)->whereDate('fecha_emision', $hoy)->where('estado','!=','anulado')->count();
        $stockBajo      = \App\Models\Producto::where('empresa_id', $empresaId)->whereColumn('stock_actual','<=','stock_minimo')->count();
        $ordenesPendientes = \App\Models\OrdenTrabajo::where('empresa_id', $empresaId)->whereIn('estado',['pendiente','en_proceso'])->count();

        $cotizaciones = \App\Models\Cotizacion::where('empresa_id', $empresaId)->get();

        $ventasPorDia = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $total = \App\Models\Venta::where('empresa_id', $empresaId)->whereDate('fecha_emision', $fecha->toDateString())->where('estado','!=','anulado')->sum('total');
            $ventasPorDia[] = ['dia' => $fecha->locale('es')->isoFormat('ddd D'), 'total' => round($total, 2)];
        }

        $topProductos = \App\Models\Producto::where('empresa_id', $empresaId)->orderByDesc('stock_actual')->limit(5)->get()->map(fn($p) => ['descripcion' => $p->descripcion, 'total_cantidad' => $p->stock_actual, 'total_monto' => $p->precio_venta * $p->stock_actual]);

        $ordenesRecientes = \App\Models\OrdenTrabajo::where('empresa_id', $empresaId)->orderByDesc('created_at')->limit(5)->get();

        return Inertia::render('Dashboard/Ferreteria', [
            'industry_name'    => $industryName,
            'stats' => [
                'ventas_hoy'              => $ventasHoy,
                'ventas_hoy_count'        => $ventasHoyCount,
                'ventas_mes'              => $ventasMes,
                'stock_bajo'              => $stockBajo,
                'ordenes_pendientes'      => $ordenesPendientes,
                'cotizaciones_total'      => $cotizaciones->count(),
                'cotizaciones_aprobadas'  => $cotizaciones->where('estado','aprobada')->count(),
                'cotizaciones_enviadas'   => $cotizaciones->where('estado','enviada')->count(),
                'cotizaciones_monto'      => round($cotizaciones->where('estado','aprobada')->sum('total'), 2),
            ],
            'ventas_por_dia'    => $ventasPorDia,
            'top_productos'     => $topProductos,
            'ordenes_recientes' => $ordenesRecientes,
        ]);
    }

    if ($industryType === 'farmacia') {
        // ── Dashboard Farmacia ──
        $empresaId = auth()->user()->empresa_id;
        $hoy  = now()->toDateString();
        $mes  = now()->month;
        $anio = now()->year;

        $ventasHoy = \App\Models\Venta::where('empresa_id', $empresaId)
            ->whereDate('fecha_emision', $hoy)->where('estado','!=','anulado')->sum('total');

        $ventasMes = \App\Models\Venta::where('empresa_id', $empresaId)
            ->whereMonth('fecha_emision', $mes)->whereYear('fecha_emision', $anio)->where('estado','!=','anulado')->sum('total');

        $totalVentas = \App\Models\Venta::where('empresa_id', $empresaId)->where('estado','!=','anulado')->count();

        $ventasHoyCount = \App\Models\Venta::where('empresa_id', $empresaId)
            ->whereDate('fecha_emision', $hoy)->where('estado','!=','anulado')->count();

        $productosStockBajo = \App\Models\Producto::where('empresa_id', $empresaId)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')->count();

        // Vencimientos
        $vencidosCount = \App\Models\Producto::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', now()->toDateString())
            ->count();

        $porVencerCount = \App\Models\Producto::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->count();

        $ventasPorDia = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $total = \App\Models\Venta::where('empresa_id', $empresaId)
                ->whereDate('fecha_emision', $fecha->toDateString())->where('estado','!=','anulado')->sum('total');
            $ventasPorDia[] = [
                'dia'   => $fecha->locale('es')->isoFormat('ddd D'),
                'total' => round($total, 2),
            ];
        }

        $topProductos = \App\Models\VentaDetalle::selectRaw('descripcion, SUM(cantidad) as total_cantidad, SUM(total) as total_monto')
            ->whereHas('venta', fn($q) => $q->where('empresa_id', $empresaId))
            ->groupBy('descripcion')
            ->orderByDesc('total_monto')
            ->limit(5)->get();

        $ultimasVentas = \App\Models\Venta::where('empresa_id', $empresaId)
            ->orderBy('created_at', 'desc')->limit(5)->get();

        return Inertia::render('Dashboard/Farmacia', [
            'industry_name'    => $industryName,
            'vencidos_count'   => $vencidosCount,
            'por_vencer_count' => $porVencerCount,
            'stats' => [
                'ventas_hoy'         => $ventasHoy,
                'ventas_mes'         => $ventasMes,
                'total_ventas'       => $totalVentas,
                'ventas_hoy_count'   => $ventasHoyCount,
                'stock_bajo'         => $productosStockBajo,
            ],
            'ventas_por_dia'  => $ventasPorDia,
            'top_productos'   => $topProductos,
            'ultimas_ventas'  => $ultimasVentas,
        ]);
    }

    if ($industryType === 'minimarket') {
        // ── Dashboard Minimarket ──
        $hoy  = now()->toDateString();
        $mes  = now()->month;
        $anio = now()->year;
        $empresaId = auth()->user()->empresa_id;

        $ventasHoy = \App\Models\Venta::where('empresa_id', $empresaId)
            ->whereDate('fecha_emision', $hoy)->where('estado','!=','anulado')->sum('total');

        $ventasMes = \App\Models\Venta::where('empresa_id', $empresaId)
            ->whereMonth('fecha_emision', $mes)->whereYear('fecha_emision', $anio)->where('estado','!=','anulado')->sum('total');

        $totalVentas = \App\Models\Venta::where('empresa_id', $empresaId)->where('estado','!=','anulado')->count();

        $ventasHoyCount = \App\Models\Venta::where('empresa_id', $empresaId)
            ->whereDate('fecha_emision', $hoy)->where('estado','!=','anulado')->count();

        $productosStockBajo = \App\Models\Producto::where('empresa_id', $empresaId)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')->count();

        $ventasPorDia = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $total = \App\Models\Venta::where('empresa_id', $empresaId)
                ->whereDate('fecha_emision', $fecha->toDateString())->where('estado','!=','anulado')->sum('total');
            $ventasPorDia[] = [
                'dia'   => $fecha->locale('es')->isoFormat('ddd D'),
                'total' => round($total, 2),
            ];
        }

        $topProductos = \App\Models\VentaDetalle::selectRaw('descripcion, SUM(cantidad) as total_cantidad, SUM(total) as total_monto')
            ->whereHas('venta', fn($q) => $q->where('empresa_id', $empresaId))
            ->groupBy('descripcion')
            ->orderByDesc('total_monto')
            ->limit(5)->get();

        $ultimasVentas = \App\Models\Venta::where('empresa_id', $empresaId)
            ->orderBy('created_at', 'desc')->limit(5)->get();

        return Inertia::render('Dashboard/Minimarket', [
            'industry_name' => $industryName,
            'stats' => [
                'ventas_hoy'         => $ventasHoy,
                'ventas_mes'         => $ventasMes,
                'total_ventas'       => $totalVentas,
                'ventas_hoy_count'   => $ventasHoyCount,
                'stock_bajo'         => $productosStockBajo,
            ],
            'ventas_por_dia'  => $ventasPorDia,
            'top_productos'   => $topProductos,
            'ultimas_ventas'  => $ultimasVentas,
        ]);
    }

    // ── Dashboard Restaurante (default) ──
    $hoy  = now()->toDateString();
    $mes  = now()->month;
    $anio = now()->year;

   $empresaId     = auth()->user()->empresa_id;
$mesasEmpresa  = \App\Models\Mesa::where('empresa_id', $empresaId)->pluck('id');

$ventasHoy     = \App\Models\CajaRestaurante::whereIn('mesa_id', $mesasEmpresa)->whereDate('created_at', $hoy)->sum('total');
$ventasMes     = \App\Models\CajaRestaurante::whereIn('mesa_id', $mesasEmpresa)->whereMonth('created_at', $mes)->whereYear('created_at', $anio)->sum('total');
$mesasHoy      = \App\Models\CajaRestaurante::whereIn('mesa_id', $mesasEmpresa)->whereDate('created_at', $hoy)->count();
$mesasOcupadas = \App\Models\Mesa::where('empresa_id', $empresaId)->where('estado', 'ocupada')->count();
$mesasLibres   = \App\Models\Mesa::where('empresa_id', $empresaId)->where('estado', 'libre')->count();
$totalMesas    = \App\Models\Mesa::where('empresa_id', $empresaId)->count();
$pedidosCocina = \App\Models\Pedido::whereIn('mesa_id', $mesasEmpresa)->where('estado', 'enviado')->count();

    $ventasPorDia = [];
    for ($i = 6; $i >= 0; $i--) {
        $fecha = now()->subDays($i);
        $total = \App\Models\CajaRestaurante::whereIn('mesa_id', $mesasEmpresa)->whereDate('created_at', $fecha->toDateString())->sum('total');
        $ventasPorDia[] = ['dia' => $fecha->locale('es')->isoFormat('ddd D'), 'total' => round($total, 2)];
    }

    $ventasPorMes = [];
    for ($i = 5; $i >= 0; $i--) {
        $fecha = now()->subMonths($i);
        $total = \App\Models\CajaRestaurante::whereMonth('created_at', $fecha->month)->whereYear('created_at', $fecha->year)->sum('total');
        $ventasPorMes[] = ['mes' => $fecha->locale('es')->isoFormat('MMM'), 'total' => round($total, 2)];
    }

    $topPlatos    = \App\Models\PedidoDetalle::selectRaw('nombre_producto, SUM(cantidad) as total_cantidad, SUM(subtotal) as total_monto')->groupBy('nombre_producto')->orderByDesc('total_monto')->limit(5)->get();
    $metodosPago = \App\Models\CajaRestaurante::whereIn('mesa_id', $mesasEmpresa)->whereDate('created_at', $hoy);
    $ultimosCobros = \App\Models\CajaRestaurante::with('mesa')
    ->whereIn('mesa_id', $mesasEmpresa)
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

    $industryName = ucfirst(auth()->user()->empresa->industry_type ?? 'restaurante');

    return Inertia::render('Dashboard/Index', [
        'industry_name'  => $industryName,
        'stats' => [
            'ventas_hoy'     => $ventasHoy,
            'ventas_mes'     => $ventasMes,
            'mesas_hoy'      => $mesasHoy,
            'mesas_ocupadas' => $mesasOcupadas,
            'mesas_libres'   => $mesasLibres,
            'total_mesas'    => $totalMesas,
            'pedidos_cocina' => $pedidosCocina,
        ],
        'ventas_por_dia' => $ventasPorDia,
        'ventas_por_mes' => $ventasPorMes,
        'top_platos'     => $topPlatos,
        'metodos_pago'   => $metodosPago,
        'ultimos_cobros' => $ultimosCobros,
    ]);
})->name('dashboard');

    // Clientes
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
    Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
    Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy');

    // Productos
    Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
    Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
    Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');
    Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');

    // Ventas
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/crear', [VentaController::class, 'create'])->name('ventas.create');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::get('/ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show');
    Route::post('/ventas/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular');
    Route::post('/ventas/{venta}/enviar-sunat', [VentaController::class, 'enviarSunat'])->name('ventas.enviar-sunat');
    Route::post('/ventas/{venta}/enviar-email', [VentaController::class, 'enviarEmail'])->name('ventas.enviar-email');
    Route::get('/ventas/{venta}/pdf-ticket', [VentaController::class, 'pdfTicket'])->name('ventas.pdf-ticket');
    Route::get('/ventas/{venta}/pdf-a4', [VentaController::class, 'pdfA4'])->name('ventas.pdf-a4');

// Proveedores
    Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');
    Route::post('/proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');
    Route::put('/proveedores/{proveedor}', [ProveedorController::class, 'update'])->name('proveedores.update');
    Route::delete('/proveedores/{proveedor}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');

// Caja
    Route::get('/caja', [CajaController::class, 'index'])->name('caja.index');
    Route::post('/caja/abrir', [CajaController::class, 'abrir'])->name('caja.abrir');
    Route::post('/caja/movimiento', [CajaController::class, 'agregarMovimiento'])->name('caja.movimiento');
    Route::post('/caja/cerrar', [CajaController::class, 'cerrar'])->name('caja.cerrar');

// Notas de crédito
    Route::get('/notas-credito', [NotaCreditoController::class, 'index'])->name('notas-credito.index');
    Route::get('/ventas/{venta}/nota-credito', [NotaCreditoController::class, 'create'])->name('notas-credito.create');
    Route::post('/ventas/{venta}/nota-credito', [NotaCreditoController::class, 'store'])->name('notas-credito.store');
    Route::get('/notas-credito/{notaCredito}', [NotaCreditoController::class, 'show'])->name('notas-credito.show');

// Configuración
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::post('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');
    Route::post('/configuracion/nubefact', [ConfiguracionController::class, 'updateNubefact'])->name('configuracion.nubefact');
    Route::get('/configuracion/nubefact/test', [ConfiguracionController::class, 'testNubefact'])->name('configuracion.nubefact.test');

// Usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
    Route::patch('/usuarios/{usuario}/toggle', [UsuarioController::class, 'toggleActivo'])->name('usuarios.toggle');

// Compras
    Route::get('/compras', [CompraController::class, 'index'])->name('compras.index');
    Route::get('/compras/crear', [CompraController::class, 'create'])->name('compras.create');
    Route::post('/compras', [CompraController::class, 'store'])->name('compras.store');
    Route::get('/compras/{compra}', [CompraController::class, 'show'])->name('compras.show');
    Route::post('/compras/{compra}/anular', [CompraController::class, 'anular'])->name('compras.anular');

// Consultas SUNAT/RENIEC
    Route::get('/api/consulta/ruc/{ruc}', [ConsultaController::class, 'consultarRuc'])->name('api.ruc');
    Route::get('/api/consulta/dni/{dni}', [ConsultaController::class, 'consultarDni'])->name('api.dni');

// Reportes
    Route::get('/reportes', [ReporteController::class, 'ventas'])->name('reportes.ventas');
    Route::get('/reportes/exportar-excel', [ReporteController::class, 'exportarExcel'])->name('reportes.excel');
    Route::get('/reportes/exportar-ple', [ReporteController::class, 'exportarPle'])->name('reportes.ple');

}); // Cierre del middleware auth principal

// Cotizaciones
    Route::get('/cotizaciones', [CotizacionController::class, 'index'])->name('cotizaciones.index');
    Route::get('/cotizaciones/crear', [CotizacionController::class, 'create'])->name('cotizaciones.create');
    Route::post('/cotizaciones', [CotizacionController::class, 'store'])->name('cotizaciones.store');
    Route::get('/cotizaciones/{cotizacion}', [CotizacionController::class, 'show'])->name('cotizaciones.show');
    Route::post('/cotizaciones/{cotizacion}/convertir', [CotizacionController::class, 'convertir'])->name('cotizaciones.convertir');
    Route::delete('/cotizaciones/{cotizacion}', [CotizacionController::class, 'destroy'])->name('cotizaciones.destroy');
   Route::get('/cotizaciones/{cotizacion}/pdf', [CotizacionController::class, 'pdf'])->name('cotizaciones.pdf');
  Route::post('/cotizaciones/{cotizacion}/estado', [CotizacionController::class, 'cambiarEstado'])->name('cotizaciones.estado');

// Mesas
    Route::get('/mesas', [MesaController::class, 'index'])->name('mesas.index');
    Route::post('/mesas', [MesaController::class, 'store'])->name('mesas.store');
    Route::put('/mesas/{mesa}', [MesaController::class, 'update'])->name('mesas.update');
    Route::delete('/mesas/{mesa}', [MesaController::class, 'destroy'])->name('mesas.destroy');
    Route::post('/mesas/{mesa}/estado', [MesaController::class, 'cambiarEstado'])->name('mesas.estado');

    // ── Menú / Carta ──────────────────────────────────────────
Route::prefix('menu')->name('menu.')->middleware(['auth'])->group(function () {

    // Categorías
    Route::get('/',                         [MenuCategoriaController::class, 'index'])->name('index');
    Route::post('/categorias',              [MenuCategoriaController::class, 'store'])->name('categorias.store');
    Route::put('/categorias/{menuCategoria}',[MenuCategoriaController::class, 'update'])->name('categorias.update');
    Route::delete('/categorias/{menuCategoria}',[MenuCategoriaController::class, 'destroy'])->name('categorias.destroy');

    // Productos
    Route::post('/productos',               [MenuProductoController::class, 'store'])->name('productos.store');
    Route::put('/productos/{menuProducto}', [MenuProductoController::class, 'update'])->name('productos.update');
    Route::delete('/productos/{menuProducto}',[MenuProductoController::class, 'destroy'])->name('productos.destroy');
    Route::patch('/productos/{menuProducto}/disponible', [MenuProductoController::class, 'toggleDisponible'])->name('productos.disponible');
});

// API pública para POS
Route::get('/api/carta', [MenuCategoriaController::class, 'apiCarta'])->middleware('auth');

// Mesas
    Route::get('/mesas', [MesaController::class, 'index'])->name('mesas.index');
    Route::post('/mesas', [MesaController::class, 'store'])->name('mesas.store');
    Route::put('/mesas/{mesa}', [MesaController::class, 'update'])->name('mesas.update');
    Route::delete('/mesas/{mesa}', [MesaController::class, 'destroy'])->name('mesas.destroy');
    Route::post('/mesas/{mesa}/estado', [MesaController::class, 'cambiarEstado'])->name('mesas.estado');

    // Menú / Carta

    Route::get('/api/carta', [MenuCategoriaController::class, 'apiCarta'])->name('api.carta');

    // POS
    Route::get('/pos/{mesa}',   [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/{mesa}',  [PosController::class, 'store'])->name('pos.store');
    Route::post('/pos/{mesa}/cerrar', [PosController::class, 'cerrar'])->name('pos.cerrar');


// Cocina
Route::get('/cocina',                          [CocinaController::class, 'index'])->name('cocina.index')->middleware('rol:cocina,admin');
Route::post('/cocina/{pedido}/listo',          [CocinaController::class, 'marcarListo'])->name('cocina.listo')->middleware('rol:cocina,admin');
Route::post('/cocina/detalle/{pedidoDetalle}/listo', [CocinaController::class, 'marcarDetalleListo'])->name('cocina.detalle.listo')->middleware('rol:cocina,admin');

// Caja Restaurante
Route::get('/caja-restaurante/{mesa}',   [CajaRestauranteController::class, 'show'])->name('caja-restaurante.show');
Route::post('/caja-restaurante/{mesa}',  [CajaRestauranteController::class, 'cobrar'])->name('caja-restaurante.cobrar');

// Turnos
Route::get('/turnos',           [TurnoController::class, 'index'])->name('turnos.index');
Route::post('/turnos/abrir',    [TurnoController::class, 'abrir'])->name('turnos.abrir');
Route::post('/turnos/{turno}/cerrar', [TurnoController::class, 'cerrar'])->name('turnos.cerrar');
Route::get('/turnos/{turno}',   [TurnoController::class, 'show'])->name('turnos.show');


Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/mozos',                   [MozoController::class, 'index'])->name('mozos.index');
        Route::post('/mozos',                  [MozoController::class, 'store'])->name('mozos.store');
        Route::get('/mozos/{mozo}',            [MozoController::class, 'show'])->name('mozos.show');
        Route::put('/mozos/{mozo}',            [MozoController::class, 'update'])->name('mozos.update');
        Route::patch('/mozos/{mozo}/toggle',   [MozoController::class, 'toggleActivo'])->name('mozos.toggle');
        Route::post('/mozos/{mozo}/mesas',     [MozoController::class, 'asignarMesas'])->name('mozos.mesas');
    });


// Reportes Restaurante
Route::middleware(['auth', 'admin'])->prefix('reportes-restaurante')->group(function () {
    Route::get('/', [\App\Http\Controllers\ReporteRestauranteController::class, 'index'])->name('reportes.restaurante');
    Route::get('/exportar-excel', [\App\Http\Controllers\ReporteRestauranteController::class, 'exportarExcel'])->name('reportes.restaurante.excel');
    Route::get('/exportar-pdf', [\App\Http\Controllers\ReporteRestauranteController::class, 'exportarPdf'])->name('reportes.restaurante.pdf');
});

// ==========================================
// COMANDAS (Pantalla Kanban)
// ==========================================
Route::middleware(['auth'])->prefix('comandas')->group(function () {
    Route::get('/', [\App\Http\Controllers\ComandaController::class, 'index'])->name('comandas.index');
    Route::post('/{pedido}/estado', [\App\Http\Controllers\ComandaController::class, 'cambiarEstado'])->name('comandas.estado');
    Route::post('/detalle/{detalle}/toggle', [\App\Http\Controllers\ComandaController::class, 'marcarPlatoListo'])->name('comandas.detalle.toggle');
    Route::get('/polling', [\App\Http\Controllers\ComandaController::class, 'polling'])->name('comandas.polling');
});

// ==========================================
// POS MINIMARKET
// ==========================================
Route::middleware(['auth'])->group(function () {
    Route::get('/minimarket/pos', [\App\Http\Controllers\Minimarket\PosMinimarketController::class, 'index'])->name('minimarket.pos');
    Route::post('/minimarket/pos', [\App\Http\Controllers\Minimarket\PosMinimarketController::class, 'store'])->name('minimarket.pos.store');
});

// ==========================================
// COMPROBANTES ELECTRÓNICOS SUNAT
// ==========================================
Route::middleware(['auth'])->prefix('comprobantes')->group(function () {
    Route::get('/', [\App\Http\Controllers\ComprobanteSunatController::class, 'index'])->name('comprobantes.index');
    Route::get('/caja/{caja}/crear', [\App\Http\Controllers\ComprobanteSunatController::class, 'crear'])->name('comprobantes.crear');
    Route::post('/caja/{caja}/boleta', [\App\Http\Controllers\ComprobanteSunatController::class, 'emitirBoleta'])->name('comprobantes.boleta');
    Route::post('/caja/{caja}/factura', [\App\Http\Controllers\ComprobanteSunatController::class, 'emitirFactura'])->name('comprobantes.factura');
    Route::get('/{comprobante}', [\App\Http\Controllers\ComprobanteSunatController::class, 'show'])->name('comprobantes.show');
});

// ==========================================
// RECUPERACIÓN DE CONTRASEÑA
// ==========================================
Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showLinkRequestForm'])
    ->middleware('guest')
    ->name('password.request');

Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLinkEmail'])
    ->middleware('guest')
    ->name('password.email');

Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showResetForm'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('/reset-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])
    ->middleware('guest')
    ->name('password.update');

    // Ventas Minimarket
Route::middleware(['auth'])->group(function () {
    Route::get('/minimarket/ventas', [\App\Http\Controllers\Minimarket\VentasMinimarketController::class, 'index'])->name('minimarket.ventas');
    Route::get('/minimarket/ventas/{id}', [\App\Http\Controllers\Minimarket\VentasMinimarketController::class, 'show'])->name('minimarket.ventas.show');
    Route::post('/minimarket/ventas/{id}/reintentar', [\App\Http\Controllers\Minimarket\VentasMinimarketController::class, 'reintentar'])->name('minimarket.ventas.reintentar');
    Route::post('/minimarket/ventas/{id}/anular', [\App\Http\Controllers\Minimarket\VentasMinimarketController::class, 'anular'])->name('minimarket.ventas.anular');
});

// Productos Minimarket (solo admin)
Route::middleware(['auth', 'solo_admin'])->group(function () {
    Route::get('/minimarket/productos', [\App\Http\Controllers\Minimarket\ProductosMinimarketController::class, 'index'])->name('minimarket.productos');
    Route::post('/minimarket/productos', [\App\Http\Controllers\Minimarket\ProductosMinimarketController::class, 'store'])->name('minimarket.productos.store');
    Route::put('/minimarket/productos/{producto}', [\App\Http\Controllers\Minimarket\ProductosMinimarketController::class, 'update'])->name('minimarket.productos.update');
    Route::post('/minimarket/productos/{producto}/stock', [\App\Http\Controllers\Minimarket\ProductosMinimarketController::class, 'ajustarStock'])->name('minimarket.productos.stock');
    Route::delete('/minimarket/productos/{producto}', [\App\Http\Controllers\Minimarket\ProductosMinimarketController::class, 'destroy'])->name('minimarket.productos.destroy');
    Route::get('/minimarket/categorias', [\App\Http\Controllers\Minimarket\CategoriasMinimarketController::class, 'index'])->name('minimarket.categorias');
    Route::post('/minimarket/categorias', [\App\Http\Controllers\Minimarket\CategoriasMinimarketController::class, 'store'])->name('minimarket.categorias.store');
    Route::put('/minimarket/categorias/{categoria}', [\App\Http\Controllers\Minimarket\CategoriasMinimarketController::class, 'update'])->name('minimarket.categorias.update');
    Route::delete('/minimarket/categorias/{categoria}', [\App\Http\Controllers\Minimarket\CategoriasMinimarketController::class, 'destroy'])->name('minimarket.categorias.destroy');
});

// Reportes Minimarket (solo admin)
Route::middleware(['auth', 'solo_admin'])->group(function () {
    Route::get('/minimarket/reportes', [\App\Http\Controllers\Minimarket\ReportesMinimarketController::class, 'index'])->name('minimarket.reportes');
});

// Caja Minimarket
Route::middleware(['auth'])->group(function () {
    Route::get('/minimarket/caja', [\App\Http\Controllers\Minimarket\CajaMinimarketController::class, 'index'])->name('minimarket.caja');
    Route::post('/minimarket/caja/abrir', [\App\Http\Controllers\Minimarket\CajaMinimarketController::class, 'abrir'])->name('minimarket.caja.abrir');
    Route::post('/minimarket/caja/{caja}/cerrar', [\App\Http\Controllers\Minimarket\CajaMinimarketController::class, 'cerrar'])->name('minimarket.caja.cerrar');
});

// Reportes Minimarket
Route::middleware(['auth'])->group(function () {
    Route::get('/minimarket/reportes', [\App\Http\Controllers\Minimarket\ReportesMinimarketController::class, 'index'])->name('minimarket.reportes');
});

// Proveedores
Route::middleware(['auth'])->group(function () {
    Route::get('/proveedores', [App\Http\Controllers\ProveedorController::class, 'index'])->name('proveedores.index');
    Route::post('/proveedores', [App\Http\Controllers\ProveedorController::class, 'store'])->name('proveedores.store');
    Route::put('/proveedores/{proveedor}', [App\Http\Controllers\ProveedorController::class, 'update'])->name('proveedores.update');
    Route::delete('/proveedores/{proveedor}', [App\Http\Controllers\ProveedorController::class, 'destroy'])->name('proveedores.destroy');
});

// ══ FERRETERÍA ══
Route::middleware(['auth'])->group(function () {
    Route::get('/ferreteria/productos',              [\App\Http\Controllers\Ferreteria\ProductosFerretoriaController::class, 'index'])->name('ferreteria.productos');
    Route::post('/ferreteria/productos',             [\App\Http\Controllers\Ferreteria\ProductosFerretoriaController::class, 'store'])->name('ferreteria.productos.store');
    Route::put('/ferreteria/productos/{producto}',   [\App\Http\Controllers\Ferreteria\ProductosFerretoriaController::class, 'update'])->name('ferreteria.productos.update');
    Route::post('/ferreteria/productos/{producto}/stock', [\App\Http\Controllers\Ferreteria\ProductosFerretoriaController::class, 'ajustarStock'])->name('ferreteria.productos.stock');
    Route::delete('/ferreteria/productos/{producto}',[\App\Http\Controllers\Ferreteria\ProductosFerretoriaController::class, 'destroy'])->name('ferreteria.productos.destroy');
});
    Route::get('/ferreteria/categorias',             [\App\Http\Controllers\Ferreteria\CategoriasFerretoriaController::class, 'index'])->name('ferreteria.categorias');
    Route::post('/ferreteria/categorias',            [\App\Http\Controllers\Ferreteria\CategoriasFerretoriaController::class, 'store'])->name('ferreteria.categorias.store');
    Route::put('/ferreteria/categorias/{categoria}', [\App\Http\Controllers\Ferreteria\CategoriasFerretoriaController::class, 'update'])->name('ferreteria.categorias.update');
    Route::delete('/ferreteria/categorias/{categoria}', [\App\Http\Controllers\Ferreteria\CategoriasFerretoriaController::class, 'destroy'])->name('ferreteria.categorias.destroy');
    Route::get('/ferreteria/pos',  [\App\Http\Controllers\Ferreteria\PosFerretoriaController::class, 'index'])->name('ferreteria.pos');
    Route::post('/ferreteria/pos', [\App\Http\Controllers\Ferreteria\PosFerretoriaController::class, 'store'])->name('ferreteria.pos.store');
    Route::get('/ferreteria/clientes',             [\App\Http\Controllers\Ferreteria\ClientesFerretoriaController::class, 'index'])->name('ferreteria.clientes');
    Route::post('/ferreteria/clientes',            [\App\Http\Controllers\Ferreteria\ClientesFerretoriaController::class, 'store'])->name('ferreteria.clientes.store');
    Route::put('/ferreteria/clientes/{cliente}',   [\App\Http\Controllers\Ferreteria\ClientesFerretoriaController::class, 'update'])->name('ferreteria.clientes.update');
    Route::delete('/ferreteria/clientes/{cliente}',[\App\Http\Controllers\Ferreteria\ClientesFerretoriaController::class, 'destroy'])->name('ferreteria.clientes.destroy');
    Route::get('/ferreteria/proveedores',               [\App\Http\Controllers\Ferreteria\ProveedoresFerretoriaController::class, 'index'])->name('ferreteria.proveedores');
    Route::post('/ferreteria/proveedores',              [\App\Http\Controllers\Ferreteria\ProveedoresFerretoriaController::class, 'store'])->name('ferreteria.proveedores.store');
    Route::put('/ferreteria/proveedores/{proveedor}',   [\App\Http\Controllers\Ferreteria\ProveedoresFerretoriaController::class, 'update'])->name('ferreteria.proveedores.update');
    Route::delete('/ferreteria/proveedores/{proveedor}',[\App\Http\Controllers\Ferreteria\ProveedoresFerretoriaController::class, 'destroy'])->name('ferreteria.proveedores.destroy');
    Route::get('/ferreteria/cotizaciones',                    [\App\Http\Controllers\Ferreteria\CotizacionesFerretoriaController::class, 'index'])->name('ferreteria.cotizaciones');
    Route::post('/ferreteria/cotizaciones',                   [\App\Http\Controllers\Ferreteria\CotizacionesFerretoriaController::class, 'store'])->name('ferreteria.cotizaciones.store');
    Route::patch('/ferreteria/cotizaciones/{cotizacion}/estado', [\App\Http\Controllers\Ferreteria\CotizacionesFerretoriaController::class, 'cambiarEstado'])->name('ferreteria.cotizaciones.estado');
    Route::delete('/ferreteria/cotizaciones/{cotizacion}',    [\App\Http\Controllers\Ferreteria\CotizacionesFerretoriaController::class, 'destroy'])->name('ferreteria.cotizaciones.destroy');
    Route::get('/ferreteria/ordenes',                  [\App\Http\Controllers\Ferreteria\OrdenesFerretoriaController::class, 'index'])->name('ferreteria.ordenes');
    Route::post('/ferreteria/ordenes',                 [\App\Http\Controllers\Ferreteria\OrdenesFerretoriaController::class, 'store'])->name('ferreteria.ordenes.store');
    Route::put('/ferreteria/ordenes/{orden}',          [\App\Http\Controllers\Ferreteria\OrdenesFerretoriaController::class, 'update'])->name('ferreteria.ordenes.update');
    Route::patch('/ferreteria/ordenes/{orden}/estado', [\App\Http\Controllers\Ferreteria\OrdenesFerretoriaController::class, 'cambiarEstado'])->name('ferreteria.ordenes.estado');
    Route::delete('/ferreteria/ordenes/{orden}',       [\App\Http\Controllers\Ferreteria\OrdenesFerretoriaController::class, 'destroy'])->name('ferreteria.ordenes.destroy');
    Route::get('/ferreteria/garantias',                    [\App\Http\Controllers\Ferreteria\GarantiasFerretoriaController::class, 'index'])->name('ferreteria.garantias');
    Route::post('/ferreteria/garantias',                   [\App\Http\Controllers\Ferreteria\GarantiasFerretoriaController::class, 'store'])->name('ferreteria.garantias.store');
    Route::put('/ferreteria/garantias/{garantia}',         [\App\Http\Controllers\Ferreteria\GarantiasFerretoriaController::class, 'update'])->name('ferreteria.garantias.update');
    Route::patch('/ferreteria/garantias/{garantia}/estado',[\App\Http\Controllers\Ferreteria\GarantiasFerretoriaController::class, 'cambiarEstado'])->name('ferreteria.garantias.estado');
    Route::delete('/ferreteria/garantias/{garantia}',      [\App\Http\Controllers\Ferreteria\GarantiasFerretoriaController::class, 'destroy'])->name('ferreteria.garantias.destroy');
    Route::get('/ferreteria/caja',                [\App\Http\Controllers\Ferreteria\CajaFerretoriaController::class, 'index'])->name('ferreteria.caja');
    Route::post('/ferreteria/caja/abrir',         [\App\Http\Controllers\Ferreteria\CajaFerretoriaController::class, 'abrir'])->name('ferreteria.caja.abrir');
    Route::post('/ferreteria/caja/{caja}/cerrar', [\App\Http\Controllers\Ferreteria\CajaFerretoriaController::class, 'cerrar'])->name('ferreteria.caja.cerrar');
    Route::get('/ferreteria/reportes', [\App\Http\Controllers\Ferreteria\ReportesFerretoriaController::class, 'index'])->name('ferreteria.reportes');

// Configuración facturación
Route::middleware(['auth'])->group(function () {
    Route::get('/configuracion/facturacion', [\App\Http\Controllers\ConfiguracionFacturacionController::class, 'index'])->name('configuracion.facturacion');
    Route::post('/configuracion/facturacion', [\App\Http\Controllers\ConfiguracionFacturacionController::class, 'guardar'])->name('configuracion.facturacion.guardar');
    Route::post('/configuracion/facturacion/probar', [\App\Http\Controllers\ConfiguracionFacturacionController::class, 'probar'])->name('configuracion.facturacion.probar');
});

// API Onboarding - recibe registro desde nexposolution.com
Route::post('/api/onboarding/crear-empresa', [\App\Http\Controllers\Api\OnboardingApiController::class, 'crearEmpresa']);

// ═══════════════════════════════════
// FARMACIA
// ═══════════════════════════════════
Route::middleware(['auth'])->prefix('farmacia')->name('farmacia.')->group(function () {
    // POS
    Route::get('/pos', [\App\Http\Controllers\Farmacia\PosFarmaciaController::class, 'index'])->name('pos');
    Route::post('/pos', [\App\Http\Controllers\Farmacia\PosFarmaciaController::class, 'store'])->name('pos.store');

    // Productos
    Route::get('/productos', [\App\Http\Controllers\Farmacia\ProductosFarmaciaController::class, 'index'])->name('productos');
    Route::post('/productos', [\App\Http\Controllers\Farmacia\ProductosFarmaciaController::class, 'store'])->name('productos.store');
    Route::put('/productos/{producto}', [\App\Http\Controllers\Farmacia\ProductosFarmaciaController::class, 'update'])->name('productos.update');
    Route::delete('/productos/{producto}', [\App\Http\Controllers\Farmacia\ProductosFarmaciaController::class, 'destroy'])->name('productos.destroy');
    Route::post('/productos/{producto}/stock', [\App\Http\Controllers\Farmacia\ProductosFarmaciaController::class, 'actualizarStock'])->name('productos.stock');

    // Vencimientos
    Route::get('/vencimientos', [\App\Http\Controllers\Farmacia\ProductosFarmaciaController::class, 'vencimientos'])->name('vencimientos');

    // Caja
    Route::get('/caja', [\App\Http\Controllers\Farmacia\CajaFarmaciaController::class, 'index'])->name('caja');
    Route::post('/caja/abrir', [\App\Http\Controllers\Farmacia\CajaFarmaciaController::class, 'abrir'])->name('caja.abrir');
    Route::post('/caja/{caja}/cerrar', [\App\Http\Controllers\Farmacia\CajaFarmaciaController::class, 'cerrar'])->name('caja.cerrar');

    // Ventas
    Route::get('/ventas', [\App\Http\Controllers\Farmacia\ReportesFarmaciaController::class, 'ventas'])->name('ventas');
    Route::get('/ventas/{venta}', [\App\Http\Controllers\Farmacia\ReportesFarmaciaController::class, 'show'])->name('ventas.show');
    Route::post('/ventas/{venta}/anular', [\App\Http\Controllers\Farmacia\ReportesFarmaciaController::class, 'anular'])->name('ventas.anular');
    Route::post('/ventas/{venta}/reintentar', [\App\Http\Controllers\Farmacia\ReportesFarmaciaController::class, 'reintentar'])->name('ventas.reintentar');

    // Reportes
    Route::get('/reportes', [\App\Http\Controllers\Farmacia\ReportesFarmaciaController::class, 'index'])->name('reportes');
});

// Categorías Farmacia
Route::middleware(['auth'])->group(function () {
    Route::get('/farmacia/categorias', [\App\Http\Controllers\Farmacia\CategoriasFarmaciaController::class, 'index'])->name('farmacia.categorias');
    Route::post('/farmacia/categorias', [\App\Http\Controllers\Farmacia\CategoriasFarmaciaController::class, 'store'])->name('farmacia.categorias.store');
    Route::put('/farmacia/categorias/{categoria}', [\App\Http\Controllers\Farmacia\CategoriasFarmaciaController::class, 'update'])->name('farmacia.categorias.update');
    Route::delete('/farmacia/categorias/{categoria}', [\App\Http\Controllers\Farmacia\CategoriasFarmaciaController::class, 'destroy'])->name('farmacia.categorias.destroy');
});

// WhatsApp API interna
Route::middleware('auth')->post('/api/whatsapp/enviar', function (\Illuminate\Http\Request $request) {
    try {
        $response = \Illuminate\Support\Facades\Http::timeout(10)
            ->post('http://127.0.0.1:3001/send', [
                'telefono' => $request->telefono,
                'mensaje'  => $request->mensaje,
            ]);
        return response()->json($response->json());
    } catch (\Exception $e) {
        return response()->json(['ok' => false, 'error' => $e->getMessage()]);
    }
});
