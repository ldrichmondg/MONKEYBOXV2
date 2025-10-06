<?php

namespace App\Services;

use App\Events\EventoClienteEnlazadoPaquete;
use App\Exceptions\ExceptionArchivosDO;
use App\Http\Requests\RequestTrackingRegistro;
use App\Models\Cliente;
use App\Models\Enum\TipoHistorialTracking;
use App\Models\Imagen;
use App\Models\Tracking;
use App\Models\TrackingHistorial;
use DateTime;
use Exception;
use http\Client\Request;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemException;

class ServicioHistorialTracking
{

    /**
     * @param $request
     * @return void
     * @throws ModelNotFoundException
     * @throws
     */
    public static function ActualizarHistoriales($request){
        // 1. Actualizar los que tienen id
        // 2. Crear los que tienen id negativo

        DB::transaction(function () use ($request) {
            $historiales = $request->input('historialesTracking', []);
            //Log::info($historiales);

            foreach ($historiales as $index => $historial) {
                $id = $historial['id'] ?? null;
                // 1. Actualizar los que tienen id
                if ($id && $id > 0){
                    $historialActualizar = TrackingHistorial::findOrFail($id);
                    $historialActualizar->DESCRIPCIONMODIFICADA = $historial['descripcionModificada'] ?? '';
                    $historialActualizar->OCULTADO = $historial['ocultado'];
                    $historialActualizar->timestamps = false;
                    $historialActualizar->save();
                } elseif ($id && $id < 0){
                    $historialNuevo = new TrackingHistorial();
                    Log::info('------');
                    Log::info($historial);
                    Log::info('------');
                    $historialNuevo->DESCRIPCION = $historial['descripcion'];
                    $historialNuevo->DESCRIPCIONMODIFICADA = $historial['descripcionModificada'] ?? '';
                    $historialNuevo->CODIGOPOSTAL =  $historial['codigoPostal'];
                    $historialNuevo->PAISESTADO =  $historial['paisEstado'];
                    $historialNuevo->OCULTADO = $historial['ocultado'];
                    $historialNuevo->TIPO = $historial['tipo'];
                    $historialNuevo->IDCOURIER = 10; //por el momento
                    $historialNuevo->IDTRACKING = $request->id;
                    $historialNuevo->save();
                }
            }
        });
    }

}
