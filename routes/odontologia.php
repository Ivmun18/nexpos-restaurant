<?php
use App\Http\Controllers\Odontologia\OdontologiaController;
use App\Http\Controllers\Odontologia\PacienteController;
use App\Http\Controllers\Odontologia\DoctorController;
use App\Http\Controllers\Odontologia\CitaController;
use App\Http\Controllers\Odontologia\PresupuestoController;
use App\Http\Controllers\Odontologia\PagoController;
use App\Http\Controllers\Odontologia\OdontogramaController;
use App\Http\Controllers\Odontologia\HistoriaClinicaController;
use App\Http\Controllers\Odontologia\RecetaController;
use App\Http\Controllers\Odontologia\LaboratorioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('odontologia')->name('odontologia.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [OdontologiaController::class, 'dashboard'])->name('dashboard');

    // Pacientes
    Route::get('/pacientes', [PacienteController::class, 'index'])->name('pacientes.index');
    Route::get('/pacientes/crear', [PacienteController::class, 'create'])->name('pacientes.create');
    Route::post('/pacientes', [PacienteController::class, 'store'])->name('pacientes.store');
    Route::get('/odontograma/{pacienteId}/pdf', [OdontogramaController::class, 'pdf'])->name('odontograma.pdf');
    Route::get('/odontograma/{pacienteId}', [OdontogramaController::class, 'show'])->name('odontograma.show');
    Route::put('/odontograma/{pacienteId}', [OdontogramaController::class, 'update'])->name('odontograma.update');
    Route::get('/pacientes/buscar', [PacienteController::class, 'buscar'])->name('pacientes.buscar');
    Route::get('/pacientes/{id}', [PacienteController::class, 'show'])->name('pacientes.show');
    Route::get('/pacientes/{id}/editar', [PacienteController::class, 'edit'])->name('pacientes.edit');
    Route::put('/pacientes/{id}', [PacienteController::class, 'update'])->name('pacientes.update');

    // Doctores
    Route::get('/doctores', [DoctorController::class, 'index'])->name('doctores.index');
    Route::post('/doctores', [DoctorController::class, 'store'])->name('doctores.store');
    Route::put('/doctores/{id}', [DoctorController::class, 'update'])->name('doctores.update');
    Route::delete('/doctores/{id}', [DoctorController::class, 'destroy'])->name('doctores.destroy');

    // Citas
    Route::get('/citas', [CitaController::class, 'index'])->name('citas.index');
    Route::post('/citas', [CitaController::class, 'store'])->name('citas.store');
    Route::put('/citas/{id}', [CitaController::class, 'update'])->name('citas.update');
    Route::delete('/citas/{id}', [CitaController::class, 'destroy'])->name('citas.destroy');

    // Historia clínica
    Route::post('/historia-clinica', [HistoriaClinicaController::class, 'store'])->name('historia.store');
    Route::post('/odontograma', [HistoriaClinicaController::class, 'guardarOdontograma'])->name('odontograma.store');

    // Recetas
    Route::post('/recetas', [RecetaController::class, 'store'])->name('recetas.store');
    Route::get('/recetas/{id}/pdf', [RecetaController::class, 'pdf'])->name('recetas.pdf');

    // Presupuestos
    Route::get('/presupuestos', [PresupuestoController::class, 'index'])->name('presupuestos.index');
    Route::post('/presupuestos', [PresupuestoController::class, 'store'])->name('presupuestos.store');
    Route::get('/presupuestos/{id}/pdf', [PresupuestoController::class, 'pdf'])->name('presupuestos.pdf');
    Route::put('/presupuestos/{id}', [PresupuestoController::class, 'update'])->name('presupuestos.update');
    Route::get('/catalogo-tratamientos', [PresupuestoController::class, 'catalogo'])->name('catalogo');
    Route::post('/catalogo-tratamientos', [PresupuestoController::class, 'storeCatalogo'])->name('catalogo.store');

    // Pagos
    Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::post('/pagos', [PagoController::class, 'store'])->name('pagos.store');
    Route::post('/pagos/cuota/{cuotaId}', [PagoController::class, 'pagarCuota'])->name('pagos.cuota');
    Route::get('/pagos/caja', [PagoController::class, 'caja'])->name('pagos.caja');
    Route::get('/pagos/{pagoId}/ticket-adelanto', [PagoController::class, 'ticketAdelanto'])->name('pagos.ticket_adelanto');
    Route::post('/pagos/recibo-cobro', [PagoController::class, 'reciboCobroPDF'])->name('pagos.recibo_cobro');
    Route::post('/pagos/adelanto', [PagoController::class, 'registrarAdelanto'])->name('pagos.adelanto');
    Route::post('/pagos/cobro-rapido', [PagoController::class, 'cobroRapido'])->name('pagos.cobro_rapido');
    Route::get('/pagos/presupuestos-paciente/{pacienteId}', [PagoController::class, 'presupuestosPaciente'])->name('pagos.presupuestos_paciente');

    // Laboratorio y proveedores
    Route::get('/laboratorio', [LaboratorioController::class, 'index'])->name('laboratorio.index');
    Route::post('/laboratorio/pedidos', [LaboratorioController::class, 'storePedido'])->name('laboratorio.store');
    Route::put('/laboratorio/pedidos/{id}', [LaboratorioController::class, 'updatePedido'])->name('laboratorio.update');
    Route::get('/proveedores', [LaboratorioController::class, 'proveedores'])->name('proveedores.index');
    Route::post('/proveedores', [LaboratorioController::class, 'storeProveedor'])->name('proveedores.store');
    // Facturación
    Route::get('/comprobantes', [\App\Http\Controllers\Odontologia\ComprobanteController::class, 'index'])->name('facturacion.index');
    Route::post('/facturacion/emitir', [\App\Http\Controllers\Odontologia\ComprobanteController::class, 'emitir'])->name('facturacion.emitir');

    // Tratamientos catálogo
    // Radiografías
    Route::post('/radiografias', [\App\Http\Controllers\Odontologia\RadiografiasController::class, 'store'])->name('odontologia.radiografias.store');
    Route::delete('/radiografias/{id}', [\App\Http\Controllers\Odontologia\RadiografiasController::class, 'destroy'])->name('odontologia.radiografias.destroy');

    // Reportes
    Route::get('/reportes', [\App\Http\Controllers\Odontologia\ReporteController::class, 'index'])->name('odontologia.reportes');

    // Configuración clínica
    Route::get('/configuracion', [\App\Http\Controllers\Odontologia\ConfiguracionController::class, 'index'])->name('odontologia.configuracion');
    Route::put('/configuracion', [\App\Http\Controllers\Odontologia\ConfiguracionController::class, 'update'])->name('odontologia.configuracion.update');
    Route::post('/configuracion/logo', [\App\Http\Controllers\Odontologia\ConfiguracionController::class, 'uploadLogo'])->name('odontologia.configuracion.logo');

    Route::get('/pacientes/{id}/ficha-pdf', [\App\Http\Controllers\Odontologia\PacienteController::class, 'fichaPdf'])->name('odontologia.pacientes.ficha-pdf');
    Route::get('/tratamientos', [\App\Http\Controllers\Odontologia\TratamientoController::class, 'index'])->name('tratamientos.index');
    Route::post('/tratamientos', [\App\Http\Controllers\Odontologia\TratamientoController::class, 'store'])->name('tratamientos.store');
    Route::put('/tratamientos/{id}', [\App\Http\Controllers\Odontologia\TratamientoController::class, 'update'])->name('tratamientos.update');
    Route::delete('/tratamientos/{id}', [\App\Http\Controllers\Odontologia\TratamientoController::class, 'destroy'])->name('tratamientos.destroy');
    Route::get('/tratamientos/lista', [\App\Http\Controllers\Odontologia\TratamientoController::class, 'lista'])->name('tratamientos.lista');

    Route::get('/insumos', [LaboratorioController::class, 'insumos'])->name('insumos.index');
    Route::post('/insumos', [LaboratorioController::class, 'storeInsumo'])->name('insumos.store');
    Route::put('/insumos/{id}', [LaboratorioController::class, 'updateInsumo'])->name('insumos.update');
});

// Servicios Notariales
Route::middleware(['auth', 'notaria.rol'])->group(function () {
    Route::get('/notaria/servicios', [App\Http\Controllers\Notaria\ServicioNotariaController::class, 'index'])->name('notaria.servicios.index');
    Route::post('/notaria/servicios', [App\Http\Controllers\Notaria\ServicioNotariaController::class, 'store'])->name('notaria.servicios.store');
    Route::put('/notaria/servicios/{servicio}', [App\Http\Controllers\Notaria\ServicioNotariaController::class, 'update'])->name('notaria.servicios.update');
    Route::delete('/notaria/servicios/{servicio}', [App\Http\Controllers\Notaria\ServicioNotariaController::class, 'destroy'])->name('notaria.servicios.destroy');
    Route::get('/notaria/servicios/lista', [App\Http\Controllers\Notaria\ServicioNotariaController::class, 'lista'])->name('notaria.servicios.lista');
});
