<?php

use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\EstadoMBoxController;
use App\Http\Controllers\PrealertaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

// Nomenclatura a usar:
// /{modulo}/{subModulo}/{accion (vista,json,otro)}/{recursoID?}

// RUTAS TRACKING
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/tracking/consulta/vista', [TrackingController::class, 'ConsultaVista'])->name('tracking.consulta.vista');
    Route::post('/tracking/registro/guardar', [TrackingController::class, 'RegistroJson'])->name('usuario.tracking.registro.guardar');
    Route::get('/tracking/detalle/vista/{id}', [TrackingController::class, 'Detalle'])->name('usuario.tracking.detalle.vista');
    Route::get('/tracking/consulta/json', [TrackingController::class, 'ConsultaJson'])->name('usuario.tracking.consulta.json');
});

// RUTAS CONFIGURACION
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/configuracion/consultar/json', [ConfiguracionController::class, 'Consultar'])->name('usuario.configuracion.consultar.json');
});

// RUTAS ESTADOMBOX
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/estadoMBox/detalles/json', [EstadoMBoxController::class, 'DetallesJson'])->name('usuario.estadoMBox.detalles.json');
});

// RUTAS PROVEEDOR
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/proveedor/consulta/json', [ProveedorController::class, 'ConsultaJson'])->name('usuario.proveedor.consultar.json');
});

// RUTAS PREALERTA
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/prealerta/registro/json', [PrealertaController::class, 'RegistroJson'])->name('usuario.prealerta.registro.json');

    Route::post('/prealerta/actualiza/json', [PrealertaController::class, 'ActualizaJson'])->name('usuario.prealerta.actualiza.json');
});
