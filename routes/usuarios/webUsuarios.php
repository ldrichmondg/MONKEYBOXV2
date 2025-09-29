<?php

use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\EstadoMBoxController;
use App\Http\Controllers\PrealertaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\UsuarioController;
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

    Route::put('/prealerta/eliminar/json', [PrealertaController::class, 'EliminarJson'])->name('usuario.prealerta.eliminar.json');
});

//RUTAS USUARIOS
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('usuario/consulta/vista', [UsuarioController::class, 'ConsultaVista'])->name('usuario.usuario.consulta.vista');
    Route::get('usuario/consulta/json', [UsuarioController::class, 'ConsultaJson'])->name('usuario.usuario.consulta.json');

    Route::post('/usuario/eliminar/json', [UsuarioController::class, 'EliminarJson'])->name('usuario.usuario.eliminar.json');

    Route::get('/usuario/detalle/vista/{id}', [UsuarioController::class, 'DetalleVista'])->name('usuario.usuario.detalle.vista');
    Route::post('/usuario/actualiza/json', [UsuarioController::class, 'ActualizaJson'])->name('usuario.usuario.actualiza.json');

    Route::get('/usuario/registro/vista', [UsuarioController::class, 'RegistroVista'])->name('usuario.usuario.registro.vista');
    Route::post('/usuario/registro/json', [UsuarioController::class,  'RegistroJson'])->name('usuario.usuario.registro.json');
});
