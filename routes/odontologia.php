<?php
use App\Http\Controllers\Odontologia\OdontologiaController;
use App\Http\Controllers\Odontologia\PacienteController;
use App\Http\Controllers\Odontologia\DoctorController;
use App\Http\Controllers\Odontologia\CitaController;
use App\Http\Controllers\Odontologia\PresupuestoController;
use App\Http\Controllers\Odontologia\PagoController;
use App\Http\Controllers\Odontologia\HistoriaClinicaController;
use App\Http\Controllers\Odontologia\LaboratorioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('odontologia')->name('odontologia.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [OdontologiaController::class, 'dashboard'])->name('dashboard');

    // Pacientes
    Route::get('/pacientes', [PacienteController::class, 'index'])->name('pacientes.index');
    Route::get('/pacientes/crear', [PacienteController::class, 'create'])->name('pacientes.create');
    Route::post('/pacientes', [PacienteController::class, 'store'])->name('pacientes.store');
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

    // Presupuestos
    Route::get('/presupuestos', [PresupuestoController::class, 'index'])->name('presupuestos.index');
    Route::post('/presupuestos', [PresupuestoController::class, 'store'])->name('presupuestos.store');
    Route::put('/presupuestos/{id}', [PresupuestoController::class, 'update'])->name('presupuestos.update');
    Route::get('/catalogo-tratamientos', [PresupuestoController::class, 'catalogo'])->name('catalogo');
    Route::post('/catalogo-tratamientos', [PresupuestoController::class, 'storeCatalogo'])->name('catalogo.store');

    // Pagos
    Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::post('/pagos', [PagoController::class, 'store'])->name('pagos.store');
    Route::post('/pagos/cuota/{cuotaId}', [PagoController::class, 'pagarCuota'])->name('pagos.cuota');

    // Laboratorio y proveedores
    Route::get('/laboratorio', [LaboratorioController::class, 'index'])->name('laboratorio.index');
    Route::post('/laboratorio/pedidos', [LaboratorioController::class, 'storePedido'])->name('laboratorio.store');
    Route::put('/laboratorio/pedidos/{id}', [LaboratorioController::class, 'updatePedido'])->name('laboratorio.update');
    Route::get('/proveedores', [LaboratorioController::class, 'proveedores'])->name('proveedores.index');
    Route::post('/proveedores', [LaboratorioController::class, 'storeProveedor'])->name('proveedores.store');
    // Facturación
    Route::get('/facturacion', [\App\Http\Controllers\Odontologia\ComprobanteController::class, 'index'])->name('facturacion.index');
    Route::post('/facturacion/emitir', [\App\Http\Controllers\Odontologia\ComprobanteController::class, 'emitir'])->name('facturacion.emitir');

    Route::get('/insumos', [LaboratorioController::class, 'insumos'])->name('insumos.index');
    Route::post('/insumos', [LaboratorioController::class, 'storeInsumo'])->name('insumos.store');
    Route::put('/insumos/{id}', [LaboratorioController::class, 'updateInsumo'])->name('insumos.update');
});
