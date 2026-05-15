<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\CuentaPorPagar\CuentaPorPagarController;
use App\Http\Controllers\Auditoria\AuditoriaController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Cuentas por Pagar
    Route::get('/cuentas-por-pagar', [CuentaPorPagarController::class, 'index'])->name('cuentas-por-pagar.index');
    Route::get('/cuentas-por-pagar/{cuenta}', [CuentaPorPagarController::class, 'show'])->name('cuentas-por-pagar.show');
    Route::post('/cuentas-por-pagar/{cuenta}/pago', [CuentaPorPagarController::class, 'registrarPago'])->name('cuentas-por-pagar.pago');

    // Auditoría
    Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
    Route::get('/auditoria/{log}', [AuditoriaController::class, 'show'])->name('auditoria.show');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/../routes/auth.php';
