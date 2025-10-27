<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\TrackingHistorial;
use App\Models\Enum\TipoHistorialTracking;
use App\Models\Tracking;
use DateTime;
use Illuminate\Support\Carbon;

class ServicioParcelsApp
{
    public static function ObtenerTracking($numeroSeguimiento): ?array
    {
        // Se obtiene el tracking si antes se habia consultado, pero generalmente se consigue solo el UUID
        // se retorna null si hubo un problema o el tracking en sí no lo encontro/existe
        try {
            Log::info('[SPA,OT] Pasa1');
            $apiKey = config('services.parcelsapp.api_key');
            $urlSeguimiento = config('services.parcelsapp.url_seguimiento');
            $datosRespuesta = [];
            // Datos del envío
            $envios = [
                [
                    'trackingId' => $numeroSeguimiento,
                    'language' => 'es',
                    'country' => 'Costa Rica',
                ],
            ];

            // Iniciar la solicitud de seguimiento
            $respuesta = HttpService::postRequest($urlSeguimiento, [
                'apiKey' => $apiKey,
                'shipments' => $envios,
            ]);

            if ($respuesta->successful()) {
                $datosRespuesta = $respuesta->json();
                Log::info('[SPA,OT] Datos respuesta ' . json_encode($datosRespuesta));
                $uuid = $datosRespuesta['uuid'] ?? '';

                $trackingCompleto = false;

                // si no trae el uuid significa que trae el tracking completo
                if (empty($uuid) && ! empty($datosRespuesta['shipments'])) {
                    Log::info('[ServicioPA->ObtenerT] Funciona a la primera: '.json_encode($datosRespuesta));
                    $trackingCompleto = true;
                }

                // Reintentar hasta que nos de el tracking:
                // 1. 10 intentos max o si ya antes me los trae
                $intentos = 0;

                while ($intentos < 15 and ! $trackingCompleto) {
                    $datosRespuesta = ServicioParcelsApp::VerificarEstadoSeguimiento($uuid); // volver a intentar
                    $intentos++;

                    // si no trae el uuid significa que trae el tracking completo
                    if ($datosRespuesta['estado'] == 'Seguimiento completo') {
                        $trackingCompleto = true;
                        break;
                    }
                }

                if (! $trackingCompleto) {
                    Log::info('[ServicioPA->ObtenerT] Tracking Incompleto: '. json_encode($datosRespuesta));
                    throw new Exception('[ServicioPA->ObtenerT] Se hicieron los intentos al llamar a la API pero no se obtuvo un tracking');
                }

                // Ya retornó el 'done', es decir que ya proceso completamente la peticion, ya sea que el tracking haya llegado o no
                Log::info('[ServicioPA->ObtenerT] Datos respuesta: '.json_encode($datosRespuesta));
                $datosLimpios = ServicioParcelsApp::LimpiarRespuestaParcelsApp($datosRespuesta);

                Log::info('[ServicioPA->ObtenerT] PASA N2');
                return $datosLimpios;
            } else {
                throw new Exception('[ServicioPA->ObtenerT] Error en la solicitud POST: '.$respuesta->status());
            }
        } catch (Exception $e) {

            Log::error('[ServicioParcelsApp,OT] error: '.$e);

            return null;
        }
    }

    /**
     *
     * @param $uuid
     * @return array|null
     */
    public static function VerificarEstadoSeguimiento($uuid): ?array
    {
        // todo: Hacer exepcion unica para llaamda api de parcelsApp
        try {
            $apiKey = config('services.parcelsapp.api_key');
            $urlSeguimiento = config('services.parcelsapp.url_seguimiento');

            $respuesta = HttpService::getRequest($urlSeguimiento, [
                'apiKey' => $apiKey,
                'uuid' => $uuid,
            ]);

            if ($respuesta->successful()) {
                // Convertir la respuesta a array
                $datosRespuesta = $respuesta->json();
                Log::info('[SPA VES] datos respuesta: ' . json_encode($datosRespuesta));

                if ($datosRespuesta['done']) {
                    return ['estado' => 'Seguimiento completo', 'datos' => $datosRespuesta];
                } else {
                    return ['estado' => 'proceso', 'datos' => $uuid];
                }
            } else {
                throw new Exception('[ServicioPA->VES] Error en la consulta GET: '.$respuesta->body());
            }
        } catch (Exception $e) {

            Log::error('[ServicioPA->VES] error: '.$e);

            return null;
        }
    }

    public static function LimpiarRespuestaParcelsApp($dataParcelsApp): ?array
    {
        // -El proposito es limpiar lo traido por la API de Parcels App y averiguar si viene completo o con errores
        // -Si viene con errores, se retorna null
        try {

            $datos = $dataParcelsApp['datos'] ?? $dataParcelsApp;
            if (isset($datos['error'])) {
                throw new Exception('[ST->LRPA] El tracking enviado por ParcelsApp tiene el siguiente error: '.$datos['error']);
            }

            if (! isset($datos['shipments'])) {
                throw new Exception('[ST->LRPA] No aparece la propiedad de shipments: ');
            }
            $shipments = $datos['shipments'][0];

            if (isset($shipments['error'])) {
                throw new Exception('[ST->LRPA] El tracking enviado a ParcelsApp no lo encuentra: '.$shipments['error']);
            }

            return $datos;

        } catch (Exception $e) {
            Log::error('[ServicioPA->VES] Error: '.$e);

            return null;
        }
    }

    public static function SeparaLugar($lugar)
    {
        $postalCode = 0;
        $lugarSinCodigoPostal = $lugar;
        if (! empty($lugar)) {
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
     public static function ProcesarTrackingsParcelsApp(array $idsTracking): void
    {
          foreach (array_chunk($idsTracking, 50) as $chunk) {
            foreach ($chunk as $idTracking) {
                $respuestaObjeto = ServicioParcelsApp::ObtenerTracking($idTracking);
                if (! $respuestaObjeto) {
                    Log::warning('No se encontro', ['id' => $idTracking]);
                }
                $tracking = Tracking::where('IDTRACKING', $idTracking)->first();
                ServicioParcelsApp::GuardarHistorialesRecientes($respuestaObjeto,$tracking);
            }
        }

    }
    public static function GuardarHistorialesRecientes($dataParcelsApp, Tracking $tracking): void
    {

        // 1. Obtener la data parseada de ParcelsApp
        // 2. Convertirla en los arrayCouriers y shipments que necesitamos
        // 3. Crear los historiales Tracking

        try {

            $shipment = $dataParcelsApp['shipments'][0];
            $arrayCarries = $shipment['carriers'];

             // última fecha registrada para este tracking
            $ultima   = $tracking->fechaUltimoHistorial();
            $ultimaAt = $ultima ? Carbon::parse($ultima) : $tracking->created_at->copy()->startOfDay();
            $historiales = [];
            if (! empty($shipment['states'])) {
                foreach ($shipment['states'] as $state) {
                    $fechaStr = (new DateTime($state['date']))->format('Y-m-d H:i:s');
                    $fechaAt = self::parseFecha($fechaStr);
                    log::info("Fechas", ['fechaStr' => $fechaStr, 'fechaAt' => $fechaAt, 'ultimaAt' => $ultimaAt, 'ultima' => $fechaAt->gt($ultimaAt)]);
                    if ($fechaAt && (!$ultimaAt || $fechaAt->gt($ultimaAt))) {

                        $courierCodigoJson = $state['carrier'];
                        $lugar = ServicioParcelsApp::SeparaLugar(! empty($state['location']) ? $state['location'] : '');

                        $historial = new TrackingHistorial;
                        $historial->DESCRIPCION = $state['status'];
                        $historial->DESCRIPCIONMODIFICADA = '';
                        $historial->PAISESTADO = $lugar[0];
                        $historial->CODIGOPOSTAL = $lugar[1];
                        $historial->OCULTADO = false;
                        $historial->TIPO = TipoHistorialTracking::API->value;
                        $historial->created_at = (new DateTime($state['date']))->format('Y-m-d H:i:s');
                        $historial->updated_at = (new DateTime($state['date']))->format('Y-m-d H:i:s');
                        $historial->IDCOURIER = $tracking->courrierNombreAId($arrayCarries[$courierCodigoJson]);
                        $historial->IDTRACKING = $tracking->id;

                        $historiales[] = $historial;
                    }
                }
            }

            Log::info("Historiales nuevos", ['historiales' => $historiales]);
            foreach ($historiales as $historial) {
                Log::info("message", ['historial' => $historial]);
                $historial->save();
            }


        } catch (Exception $e) {

            Log::error('[ServicioTracking->ConstruirHistoriales] error: '.$e);
        }
    }

      /**
     * Parsea una fecha de la API a Carbon, devolviendo null si falla.
     *
     * @param string $fecha
     * @return Carbon|null
     */
    private static function parseFecha(string $fecha): ?Carbon
    {
        try {
            return Carbon::parse($fecha);
        } catch (\Throwable) {
            return null;
        }
    }
}
