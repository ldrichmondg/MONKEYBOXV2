<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DireccionController;
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
    Route::post('/tracking/actualiza/json', [TrackingController::class, 'ActualizaJson'])->name('usuario.tracking.actualiza.json');

    Route::post('/tracking/actualiza/json/cambioestado', [TrackingController::class, 'ActualizaEstado'])->name('usuario.tracking.json.cambioestado');
    Route::post('/tracking/actualiza/json/subirFactura', [TrackingController::class, 'SubirFactura'])->name('usuario.tracking.actualiza.json.subirfactura');
    Route::post('/tracking/actualiza/json/eliminarFactura', [TrackingController::class, 'EliminarFactura'])->name('usuario.tracking.actualiza.json.eliminarfactura');

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

//RUTAS CLIENTES
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/cliente/consulta/vista', [ClienteController::class, 'ConsultaVista'])->name('usuario.cliente.consulta.vista');
    Route::get('/cliente/consulta/json', [ClienteController::class, 'ConsultaJson'])->name('usuario.cliente.consulta.json');

    Route::post('/cliente/eliminar/json', [ClienteController::class, 'EliminarJson'])->name('usuario.cliente.eliminar.json');

    Route::get('/cliente/detalle/vista/{id}', [ClienteController::class, 'DetalleVista'])->name('usuario.cliente.detalle.vista');
    Route::get('/cliente/detalle/json/{id}', [ClienteController::class, 'DetalleJson'])->name('usuario.cliente.detalle.json');

    Route::post('/cliente/actualiza/json', [ClienteController::class, 'ActualizaJson'])->name('usuario.cliente.actualiza.json');

    Route::get('/cliente/registro/vista', [ClienteController::class, 'RegistroVista'])->name('usuario.cliente.registro.vista');
    Route::post('/cliente/registro/json', [ClienteController::class,  'RegistroJson'])->name('usuario.cliente.registro.json');
});

//RUTAS DIRECCIONES
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/canton/consulta/json/{id}', [DireccionController::class, 'ConsultaCantonesJson'])->name('usuario.cantones.consulta.json');
    Route::get('/distrito/consulta/json/{id}', [DireccionController::class, 'ConsultaDistritoJson'])->name('usuario.distrito.consulta.json');
    Route::get('/distrito/consulta/json/codigoPostal/{id}', [DireccionController::class, 'ConsultaCodigoPostalJson'])->name('usuario.distrito.consulta.codigopostal.json');
    Route::get('/codigopostal/detalle/json/{codigopostal}', [DireccionController::class, 'DetalleCodigoPostalJson'])->name('usuario.codigopostal.detalle.json');

    Route::post('/direccion/consulta/json/obtenerTrackings', [DireccionController::class, 'ConsultaTrackings'])->name('usuario.direccion.consulta.json.trackings');

    //Route::get('/cliente/consulta/json', [ClienteController::class, 'ConsultaJson'])->name('usuario.cliente.consulta.json');
    //Route::post('/usuario/registro/json', [UsuarioController::class,  'RegistroJson'])->name('usuario.usuario.registro.json');*/
});
