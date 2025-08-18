<?php

namespace App\Services;

use App\Events\EventoClienteEnlazadoPaquete;
use App\Events\EventoCorreoEliminarTracking;
use App\Events\EventoFacturaGenerada;
use App\Events\FacturaGenerada;
use App\Http\Requests\RequestTrackingRegistro;
use Exception;
use App\Models\Tracking;
use App\Models\Direccion;
use App\Models\Cliente;
use App\Models\TrackingHistorial;
use App\Models\Enum\TipoHistorialTracking;
use Illuminate\Support\Facades\Auth;
use DateTime;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailAvisoCostaRica;
use App\Mail\EmailAvisoCliente;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ServicioTracking
{


    private static function RetornaValorAtributo($atributos, $nombre)
    {
        foreach ($atributos as $atributo) {
            if ($atributo['l'] === $nombre) {
                return $atributo['val'];
            }
        }
        return NULL;
    }

    public static function ObtenerORegistrarTracking(RequestTrackingRegistro $request): ?Tracking
    {
        // 1. Ocupo verificar si el tracking ya existe en la BD
        // 1.1 Si existe, retornarlo
        // 2. Llamar a la API de parcelsApp para que retorne t#do el historial
        try {

            // 1. Ocupo verificar si el tracking ya existe en la BD
            $tracking = Tracking::where('IDTRACKING', $request->input('idTracking'))->first();

            // 1.1 Si existe, retornarlo
            if ($tracking) return $tracking;

            // 2. Llamar a la API de parcelsApp para que retorne t#do el historial
            $trackingNuevo = ServicioTracking::ConstruirTrackingCompleto($request->idTracking, $request->idCliente);

            return $trackingNuevo;
        } catch (Exception $e) {

            Log::error('[ServicioTracking->ObtenerORegistrarTracking] error:' . $e);
            return null;
        }
    }


    public static function ConstruirTracking($dataParcelsApp, $idCliente): ?Tracking
    {
        // 1. Recibir la data de parcelsApp
        // 2. Crear el objeto tracking

        try {

            $direccionPrincipalCliente = Cliente::find($idCliente)->direccionPrincipal;
            $estadoSinPreealerta = ServicioEstadoMBox::ObtenerEstadosMBox(['Sin Preealertar'])[0];
            $shipment = $dataParcelsApp['shipments'][0];
            $attributes = $shipment['attributes'];
            $arrayCarries = $shipment['carriers'];

            $tracking = new Tracking();
            $tracking->IDAPI = 0; //por si ponemos despues el UUID
            $tracking->IDTRACKING = $shipment['trackingId'];
            $tracking->DESCRIPCION = null;
            $tracking->DESDE = ServicioTracking::RetornaValorAtributo($attributes, "from") ?? "";
            $tracking->HASTA = ServicioTracking::RetornaValorAtributo($attributes, "to") ?? "";
            $tracking->DESTINO = ServicioTracking::RetornaValorAtributo($attributes, "destination") ?? "";
            $tracking->COURIER = implode(", ", $arrayCarries);
            $tracking->DIASTRANSITO = ServicioTracking::RetornaValorAtributo($attributes, "days_transit") ?? 0;
            $tracking->PESO = 0.000;
            $tracking->IDDIRECCION = $direccionPrincipalCliente->id;
            $tracking->IDUSUARIO = Auth::user()->id ?? 1;
            $tracking->ESTADOMBOX = $estadoSinPreealerta->DESCRIPCION;
            //
            $tracking->save();

            //Log::info('Tracking listo para ret: ' . $tracking);
            return $tracking;

        } catch (Exception $e) {

            Log::error('[ServicioTracking->ConstruirTracking] error:' . $e);
            return null;
        }


    }

    public static function ConstruirHistoriales($dataParcelsApp, $tracking): ?Collection
    {
        // 1. Obtener la data parseada de ParcelsApp
        // 2. Convertirla en los arrayCouriers y shipments que necesitamos
        // 3. Crear los historiales Tracking

        try {

            $shipment = $dataParcelsApp['shipments'][0];
            $arrayCarries = $shipment['carriers'];

            $historiales = [];
            if (!empty($shipment['states'])) {
                foreach ($shipment['states'] as $state) {
                    $courierCodigoJson = $state['carrier'];
                    $lugar = ServicioParcelsApp::SeparaLugar(!empty($state['location']) ? $state['location'] : "");

                    $historial = new TrackingHistorial();
                    $historial->DESCRIPCION = $state['status'];
                    $historial->DESCRIPCIONMODIFICADA = '';
                    $historial->PAISESTADO = $lugar[0];
                    $historial->CODIGOPOSTAL = $lugar[1];
                    $historial->OCULTADO = false;
                    $historial->TIPO = TipoHistorialTracking::API->value;
                    $historial->FECHA = (new DateTime($state['date']))->format('Y-m-d H:i:s');
                    $historial->IDCOURIER = $tracking->courrierNombreAId($arrayCarries[$courierCodigoJson]);
                    $historial->IDTRACKING = $tracking->id;

                    $historiales[] = $historial;
                }
            }

            foreach ($historiales as $historial) {
                $historial->save();
            }

            $usuario = ServicioCliente::ObtenerCliente($tracking);
            if (!empty($usuario)) {
                EventoClienteEnlazadoPaquete::dispatch($tracking, $usuario);
            }

            //Log::info('[ServicioTracking->ConstruirHistoriales] Historiales antes de ser pasados a collection: ' . json_encode($historiales));
            return Collection::make($historiales);

        } catch (Exception $e) {

            Log::error('[ServicioTracking->ConstruirHistoriales] error: ' . $e);
            return null;
        }
    }

    public static function ConstruirTrackingCompleto($idTracking, $idCliente): ?Tracking
    {
        try {
            $respuestaObjeto = ServicioParcelsApp::ObtenerTracking($idTracking);

            if (!$respuestaObjeto) {
                throw new Exception("Respuesta inválida/sin datos/el tracking no se encontró");
            }

            $tracking = ServicioTracking::ConstruirTracking($respuestaObjeto, $idCliente);

            if (!$tracking) {
                throw new Exception("No se pudo construir el tracking");
            }

            $historiales = ServicioTracking::ConstruirHistoriales($respuestaObjeto, $tracking);

            if (!$historiales) {
                throw new Exception("No se pudieron construir los historiales");
            }

            $tracking->historialesT = $historiales;
            Log::info("[ServicioTracking->ConstruirTrackingCompleto] Tracking completo: " . $tracking);
            return $tracking;

        } catch (Exception $e) {

            Log::error('[ServicioTracking->ConstruirTrackingCompleto] error:' . $e);
            return null;
        }
    }


}
