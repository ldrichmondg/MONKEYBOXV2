<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class ServicioParcelsApp
{
    public static function ObtenerTracking($numeroSeguimiento) : ?array
    {
        // Se obtiene el tracking si antes se habia consultado, pero generalmente se consigue solo el UUID
        // se retorna null si hubo un problema o el tracking en sí no lo encontro/existe
        try {
            $apiKey = env('PARCELSAPP_API_KEY');
            $urlSeguimiento = env('PARCELSAPP_URL_SEGUIMIENTO');
            $datosRespuesta = [];
            // Datos del envío
            $envios = [
                [
                    'trackingId' => $numeroSeguimiento,
                    'language'   => 'es',
                    'country'    => 'Costa Rica'
                ],
            ];

            // Iniciar la solicitud de seguimiento
            $respuesta = HttpService::postRequest($urlSeguimiento, [
                'apiKey'   => $apiKey,
                'shipments' => $envios,
            ]);

            if ($respuesta->successful()) {
                $datosRespuesta = $respuesta->json();
                $uuid = $datosRespuesta['uuid'] ?? "";

                $trackingCompleto = false;

                //si no trae el uuid significa que trae el tracking completo
                if (empty($uuid) && !empty($datosRespuesta['shipments'])) {
                    Log::info('[ServicioPA->ObtenerT] Funciona a la primera: '. json_encode($datosRespuesta));
                    $trackingCompleto = true;
                }

                // Reintentar hasta que nos de el tracking:
                // 1. 10 intentos max o si ya antes me los trae
                $intentos = 0;

                while($intentos < 15 and !$trackingCompleto){
                    $datosRespuesta = ServicioParcelsApp::VerificarEstadoSeguimiento($uuid); //volver a intentar
                    $intentos++;

                    //si no trae el uuid significa que trae el tracking completo
                    if ($datosRespuesta['estado'] == 'Seguimiento completo') {
                        $trackingCompleto = true;
                        break;
                    }
                }

                if (!$trackingCompleto){
                    Log::info('[ServicioPA->ObtenerT] Tracking Incompleto: '.$datosRespuesta);
                    throw new Exception('[ServicioPA->ObtenerT] Se hicieron los intentos al llamar a la API pero no se obtuvo un tracking');
                }

                //Ya retornó el 'done', es decir que ya proceso completamente la peticion, ya sea que el tracking haya llegado o no
                Log::info('[ServicioPA->ObtenerT] Datos respuesta: '. json_encode($datosRespuesta));
                $datosLimpios = ServicioParcelsApp::LimpiarRespuestaParcelsApp($datosRespuesta);

                return $datosLimpios;
            } else {
                throw new Exception("[ServicioPA->ObtenerT] Error en la solicitud POST: " . $respuesta->status());
            }
        } catch (Exception $e) {

            Log::error('[ServicioParcelsApp,OT] error: '.$e);
            return null;
        }
    }

    public static function VerificarEstadoSeguimiento($uuid): ?array
    {
        try {
            $apiKey = env('PARCELSAPP_API_KEY');
            $urlSeguimiento = env('PARCELSAPP_URL_SEGUIMIENTO');

            $respuesta = HttpService::getRequest($urlSeguimiento, [
                'apiKey' => $apiKey,
                'uuid'   => $uuid,
            ]);

            if ($respuesta->successful()) {
                // Convertir la respuesta a array
                $datosRespuesta = $respuesta->json();

                if ($datosRespuesta['done']) {
                    return ['estado' => 'Seguimiento completo', 'datos' => $datosRespuesta];
                } else {
                    return ['estado' => 'proceso', 'datos' => $uuid];
                }
            } else {
                throw new Exception("[ServicioPA->VES] Error en la consulta GET: " . $respuesta->body());
            }
        } catch (Exception $e) {

            Log::error('[ServicioPA->VES] error: '.$e);
            return null;
        }
    }

    public static function LimpiarRespuestaParcelsApp($dataParcelsApp): ?array {
        // -El proposito es limpiar lo traido por la API de Parcels App y averiguar si viene completo o con errores
        // -Si viene con errores, se retorna null
        try{

            $datos = $dataParcelsApp['datos'] ?? $dataParcelsApp;
            if (isset($datos['error'])) {
                throw new Exception('[ST->LRPA] El tracking enviado por ParcelsApp tiene el siguiente error: '.$datos['error']);
            }

            if (!isset($datos['shipments'])) {
                throw new Exception('[ST->LRPA] No aparece la propiedad de shipments: ');
            }
            $shipments = $datos['shipments'][0];

            if (isset($shipments['error'])) {
                throw new Exception('[ST->LRPA] El tracking enviado a ParcelsApp no lo encuentra: '.$shipments['error']);
            }

            return $datos;

        }catch(Exception $e){
            Log::error('[ServicioPA->VES] Error: '.$e);
            return null;
        }
    }

    public static function SeparaLugar($lugar)
    {
        $postalCode = 0;
        $lugarSinCodigoPostal = $lugar;
        if (!empty($lugar)) {
            $ultimaComma = strrpos($lugar, ',');
            if ($ultimaComma !== false) {
                $posiblePostalCode = trim(substr($lugar, $ultimaComma + 1));
                if (is_numeric($posiblePostalCode)) {
                    $postalCode = $posiblePostalCode;
                    $lugarSinCodigoPostal = trim(substr($lugar, 0, $ultimaComma));
                }
            }
        }
        return [$lugarSinCodigoPostal, $postalCode];
    }

}
