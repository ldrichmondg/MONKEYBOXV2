<?php

use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Nomenclatura a usar:
// /{modulo}/{subModulo}/{accion (vista,json,otro)}/{recursoID?}

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

});

// RUTAS TRACKING
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/tracking/consulta/vista', [TrackingController::class, 'ConsultaVista'])->name('tracking.consulta.vista');
    Route::get('/tracking/registro/vista', [TrackingController::class, 'RegistroMasivoVista'])->name('tracking.registroMasivo.vista');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/clientes/webClientes.php';
require __DIR__.'/usuarios/webUsuarios.php';
