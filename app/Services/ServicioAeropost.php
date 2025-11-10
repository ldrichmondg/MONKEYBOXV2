<?php

namespace App\Services;

use App\Actions\ActionDepurarRegex;
use App\Exceptions\ExceptionAeropost;
use App\Exceptions\ExceptionAPCourierNoObtenido;
use App\Exceptions\ExceptionAPCouriersNoObtenidos;
use App\Exceptions\ExceptionAPObtenerPaquetes;
use App\Exceptions\ExceptionAPRequestActualizarPrealerta;
use App\Exceptions\ExceptionAPRequestEliminarPrealerta;
use App\Exceptions\ExceptionAPRequestRegistrarPrealerta;
use App\Exceptions\ExceptionAPTokenNoObtenido;
use App\Models\Enum\TipoImagen;
use App\Models\Imagen;
use App\Models\Prealerta;
use App\Models\Tracking;
use App\Models\TrackingProveedor;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\TrackingHistorial;
use App\Models\Enum\TipoHistorialTracking as EnumTipoHistorialTracking;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;


class ServicioAeropost
{


    /**
     * Procesa y sincroniza múltiples trackings contra la API de Aeropost:
     * - Obtiene token de acceso (si falla, registra y termina).
     * - Consulta detalle del paquete por IDTRACKING.
     * - Actualiza estado local (ESTADOSINCRONIZADO/ESTADOMBOX) y TrackingProveedor.
     * - Descarga y persiste historial de estados (solo entradas nuevas).
     *
     * @param array<int,string> $idsTracking Lista de IDTRACKING (códigos externos Aeropost).
     *
     * @return void
     *
     * @throws QueryException Se propaga si ocurre un error de BD fuera de los try-catch internos.
     */
    public static function ProcesarTrackingsAeropost(array $idsTracking): void
    {
        // ! se asume que los ids $idsTracking son todos de aeropost
        if (empty($idsTracking)) {
            Log::info('ProcesarTrackingsPrealertados: lista de IDs vacía.');
            return;
        }

        // 1) Autenticación + Http client
        $baseUrl = rtrim((string)config('services.aeropost.url_base'), '/');
        $http = self::buildHttpClient();

        // 2) Procesar en bloques
        foreach (array_chunk($idsTracking, 50) as $chunk) {
            foreach ($chunk as $idTracking) {
                if (!is_string($idTracking) || $idTracking === '') {
                    Log::warning('IDTRACKING inválido; se ignora.', ['id' => $idTracking]);
                    continue;
                }
                try {
                    self::procesarTrackingId($idTracking, $baseUrl, $http);
                } catch (\Throwable $e) {
                    Log::error('Error procesando tracking de Aeropost.', [
                        'id' => $idTracking,
                        'ex' => $e->getMessage(),
                    ]);
                    // Continuamos con los demás
                }
            }
        }
    }

    /**
     * Procesa un IDTRACKING concreto: detalle, actualización y su historial.
     *
     * @param string $idTracking
     * @param string $baseUrl
     * @param PendingRequest $http
     *
     * @return void
     * @throws ConnectionException
     * @throws ExceptionAPObtenerPaquetes
     * @throws ModelNotFoundException
     */
    private static function procesarTrackingId(string $idTracking, string $baseUrl, PendingRequest $http): void
    {
        // - Se asume que el idTracking es del proveedor aeropost. Si no lo es, va a tirar una excepción
        // Detalle del paquete
        $pkgData = self::obtenerDetallePaquete($baseUrl, $http, $idTracking);

        $aerotrack = Arr::get($pkgData, 'aerotrack');
        $graphicStationId = Arr::get($pkgData, 'graphicStationID');
        $weightKilos = Arr::get($pkgData, 'weightKilos');
        $images = Arr::get($pkgData, 'attachments');

        // Buscar tracking local
        $tracking = Tracking::where('IDTRACKING', $idTracking)->firstOrFail();
        //Log::info('Aeropost: procesando tracking.', ['graphicStationId' => $graphicStationId, 'tracking' => $tracking, 'weightKilos' => $weightKilos, 'payload' => $pkgData]);

        // T#odo en una transaccion por si algo falla que se deje en su estado anterior
        DB::transaction(function () use ($tracking, $aerotrack, $graphicStationId, $weightKilos, $images, $baseUrl, $http, $idTracking) {

            // 1. Actualizar estados y TrackingProveedor
            self::actualizarTrackingYProveedor($tracking, $aerotrack, $graphicStationId, $weightKilos, $images);

            // 2. Historial de estados
            if ($aerotrack) {
                $history = self::obtenerHistorial($baseUrl, $http, $aerotrack, $idTracking);
                self::nuevosHistoriales($tracking, $history);
            }
        });
    }

    /**
     * Actualiza ESTADOSINCRONIZADO/ESTADOMBOX y el TrackingProveedor (aerotrack) si aplica.
     *
     * @param Tracking $tracking
     * @param string|null $aerotrack
     * @param int $graphicStationId
     * @param float $kilos
     * @param array $imagenesAeropost
     * @return void
     * @throws ExceptionAPObtenerPaquetes
     * @throws ModelNotFoundException
     */
    private static function actualizarTrackingYProveedor(Tracking $tracking, ?string $aerotrack, int $graphicStationId, float $kilos, array $imagenesAeropost): void
    {
        // Estado sincronizado

        $estado = self::mapEstadoAeropost($graphicStationId);

        if ($estado == null) {
            throw new ExceptionAPObtenerPaquetes($tracking->IDTRACKING);
        }

        //si es el mismo estado el sincronizado con el que se le muestra al cliente
        if ($tracking->ESTADOSINCRONIZADO == $tracking->ESTADOMBOX) {
            $tracking->ESTADOMBOX = $estado;
        }
        $tracking->ESTADOSINCRONIZADO = $estado;

        // Peso
        $tracking->PESO = $kilos;

        /*Log::info('[SA, ATP] Tracking Aeropost actualizado.', [
            'id' => $tracking->IDTRACKING, 'estado' => $estado
          ]);*/
        $tracking->save();

        // Aerotrack
        if ($aerotrack) {
            $trackingProveedor = TrackingProveedor::where('IDTRACKING', $tracking->id)->firstOrFail();
            $trackingProveedor->TRACKINGPROVEEDOR = $aerotrack;
            $trackingProveedor->save();
        }

        //imagenes
        if (count($imagenesAeropost) <= 0)
            return;

        foreach ($imagenesAeropost as $imagen) {
            if ($imagen['category'] != 3)
                continue;
            //solo si son category == 3 porque esas son las fotos del paquete
            Imagen::firstOrCreate(
                [
                    'RUTA' => $imagen['url'],
                ],        // Criterios de búsqueda
                [
                    'TIPOIMAGEN' => TipoImagen::Aeropost->value,
                    'RUTA' => $imagen['url'],
                    'IDTRACKING' => $tracking->id
                ]    // Atributos para crear si no existe
            );
        }

    }
}
