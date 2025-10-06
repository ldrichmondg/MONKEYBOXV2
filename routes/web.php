<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DireccionController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Nomenclatura a usar:
// /{modulo}/{subModulo}/{accion (vista,json,otro)}/{recursoID?}

Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

});

// RUTAS TRACKING
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/tracking/registro/vista', [TrackingController::class, 'RegistroMasivoVista'])->name('tracking.registroMasivo.vista');
});

// RUTAS DIRECCION
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/direccion/consulta/json', [DireccionController::class, 'ConsultaJSON'])->name('direccion.consulta.json');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/clientes/webClientes.php';
require __DIR__.'/usuarios/webUsuarios.php';
