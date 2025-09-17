<?php

namespace App\Services;

use App\Exceptions\ExceptionAPCourierNoObtenido;
use App\Exceptions\ExceptionAPCouriersNoObtenidos;
use App\Exceptions\ExceptionAPRequestActualizarPrealerta;
use App\Exceptions\ExceptionAPRequestEliminarPrealerta;
use App\Exceptions\ExceptionAPRequestRegistrarPrealerta;
use App\Exceptions\ExceptionAPTokenNoObtenido;
use App\Exceptions\ExceptionPrealertaNotFound;
use App\Exceptions\ExceptionTrackingProveedorNotFound;
use App\Http\Requests\RequestCrearPrealerta;
use App\Models\Proveedor;
use App\Models\Tracking;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServicioPrealerta
{
    /**
     * Guarda una prealerta de cualquier proveedor
     *
     * @param RequestCrearPrealerta $request
     * @return Tracking
     * @throws QueryException
     * @throws ModelNotFoundException
     * * @throws ExceptionAPCourierNoObtenido
     * * @throws ExceptionAPCouriersNoObtenidos
     * * @throws ExceptionAPTokenNoObtenido
     * * @throws ExceptionAPRequestRegistrarPrealerta
     * * @throws ConnectionException
     */
    public static function RegistrarPrealerta(RequestCrearPrealerta $request): Tracking
    {
        // 1. Obtenemos el tracking mediante el idTracking
        // 2. Revisamos si el tracking tiene un estado = a 'sin preealertar'
        // 3. Si es igual, entonces se prealerta, sino se mantiene el estado que tiene
        // 4. Validamos el idProveedor,
        // 4.1. Si es Aeropost, se llama a AeropostService
        // 4.2. Si es MiLocker, se llama a MiLockerService (donde solo se retorna la prealerta rapido)
        // 4.3 Se pasa el estado a 'prealertado'

        // - Observaciones:
        // No se hace try-catch de QueryException porque se puede hacer un transaction para que haga rollback si algo no se hizo correctamente en la BD
        return DB::transaction(function () use ($request) {

            // 1. Obtenemos el tracking mediante el idTracking
            $tracking = Tracking::where('IDTRACKING', $request->idTracking)->firstOrFail();

            // 2. Revisamos si el tracking tiene un estado = a 'sin prealertar'
            // 3. Si es igual, entonces se prealerta, sino se mantiene el estado que tiene
            if ($tracking->estadoMBox->DESCRIPCION != 'Sin Prealertar') {
                return $tracking; // sin cambios, asi como está xq si no es sin prealertar entonces esta en otro estado
            }

            // 4. Validamos el idProveedor
            $proveedor = Proveedor::find($request->idProveedor);

            // 4.1. Si es Aeropost, se llama a AeropostService
            if ($proveedor->NOMBRE == 'Aeropost') {
                ServicioAeropost::RegistrarPrealerta($tracking, $request->valor, $request->descripcion, $request->idProveedor);

                // 4.2. Si es MiLocker, se llama a MiLockerService (donde solo se retorna la prealerta rapido)
            } elseif ($proveedor->NOMBRE == 'MiLocker') {
                ServicioMiLocker::RegistrarPrealerta($tracking->id, $request->valor, $request->descripcion, $request->idProveedor);
            }

            // 4.3 Se pasa el estado a 'prealertado'
            $tracking->ESTADOMBOX = 'Prealertado';
            $tracking->ESTADOSINCRONIZADO = 'Prealertado';
            $tracking->save();

            $tracking->load('estadoMBox', 'trackingProveedor.prealerta', 'trackingProveedor');

            return $tracking;
        });
    }

    /**
     * @param int $idTracking
     * @param string $descripcion
     * @param float $valor
     * @param int $idProveedor
     * @return void
     * @throws ExceptionPrealertaNotFound
     * @throws ExceptionTrackingProveedorNotFound
     * @throws QueryException con el firstOrFail
     * @throws ExceptionAPRequestRegistrarPrealerta
     * @throws ExceptionAPCourierNoObtenido
     * @throws ExceptionAPCouriersNoObtenidos
     * @throws ExceptionAPTokenNoObtenido
     * @throws ConnectionException
     * @throws ExceptionAPRequestActualizarPrealerta
     * @throws ExceptionAPRequestEliminarPrealerta
     */
    public static function ActualizarPrealerta(string $idTracking, string $descripcion, float $valor, int $idProveedor): void {
        // 1. Obtener el Tracking desde la BD
        // 2. Obtener el registro de la prealerta. (EX#1: No hay trackingProveedor EX#2: no hay prealerta)
        // 3. Verificar si cambiaron el idProveedor
        // 3.1. Si el proveedor es el mismo y es aeropost, actualizar la prealerta// 3.2. Si el proveedor es el mismo y es ML, actualizar la prealerta
        // 3.3. Si la cambiaron de ML -> AP, crear la prealerta.
        // 3.4. Si la cambiaron de AP -> ML, eliminar la prealerta de AP.
        // 4.0. Actualizar el registro de prealerta y trackingProveedor con los valores traidos
        // 5.0. El tracking pasan sus ambos estados a PDO

        DB::transaction(function () use ($idTracking, $descripcion, $valor, $idProveedor) {

            // 1. Obtener el Tracking desde la BD
            $tracking = Tracking::where('IDTRACKING',$idTracking)->firstOrFail();

            // 2. Obtener el registro de la prealerta. (EX#1: No hay trackingProveedor EX#2: no hay prealerta)
            if (!$tracking->trackingProveedor) {
                throw new ExceptionTrackingProveedorNotFound();
            }

            if (!$tracking->trackingProveedor->prealerta) {
                Log::info('[SP, AP] IF DE ExceptionPrealertaNotFound');
                throw new ExceptionPrealertaNotFound();
            }

            $trackingProveedor = $tracking->trackingProveedor;
            $prealerta = $tracking->trackingProveedor->prealerta;
            $proveedor = $trackingProveedor->proveedor;
            // 3. Verificar si cambiaron el idProveedor (SON IGUALES)
            if ($idProveedor == $trackingProveedor->IDPROVEEDOR) {

                // 3.1. Si el proveedor es el mismo y es aeropost, actualizar la prealerta
                if ($proveedor->NOMBRE == 'Aeropost') {
                    ServicioAeropost::ActualizarPrealerta($prealerta->IDPREALERTA, $descripcion, $valor, $tracking->IDTRACKING, $prealerta->NOMBRETIENDA, $prealerta->IDCOURIER, 2979592);
                } // 3.2. Si el proveedor es el mismo y es ML, actualizar la prealerta
                else if ($proveedor->NOMBRE == 'MiLocker') {
                    ServicioMiLocker::ActualizarPrealerta($prealerta->IDPREALERTA, $descripcion, $valor, $tracking->IDTRACKING, $prealerta->NOMBRETIENDA, $prealerta->IDCOURIER, 2979592);
                }
            } // 3-. Verificar si cambiaron el idProveedor (SON DIFERENTES)
            else {

                // 3.3. Si la cambiaron de ML -> AP, crear la prealerta.
                if ($proveedor->NOMBRE == 'MiLocker') {
                    $couriers = ServicioAeropost::ObtenerCouriers();

                    // - Obtenemos el courier de nuestro idTracking gracias al regex
                    $courierSeleccionado = ServicioAeropost::ObtenerCourier($couriers, $tracking->IDTRACKING);

                    // - Al obtener el courier, nombreTienda va a ser: Tienda de {courier}
                    $nombreTienda = 'Tienda de ' . $courierSeleccionado['name'];

                    // - Enviar el request para crear la prealerta
                    $idPrealerta = ServicioAeropost::RequestRegistrarPrealerta(2979592, $courierSeleccionado['id'], $tracking->IDTRACKING, $nombreTienda, $valor, $descripcion);

                    // - Actualizar el id de prealerta
                    $prealerta->IDPREALERTA = $idPrealerta;

                    // 3.4. Si la cambiaron de AP -> ML, eliminar la prealerta de AP.
                } else if ($proveedor->NOMBRE == 'Aeropost') {
                    ServicioAeropost::EliminarPrealerta($prealerta->IDPREALERTA);
                }

                // Actualizar el campo IDPROVEEDOR y TRACKINGPROVEEDOR de tracking proveedor
                $trackingProveedor->IDPROVEEDOR = $idProveedor;
                $trackingProveedor->TRACKINGPROVEEDOR = null;
                $trackingProveedor->save();
            }

            // 4.0 Actualizar el registro de prealerta y trackingProveedor con los valores traidos
            $prealerta->DESCRIPCION = $descripcion;
            $prealerta->VALOR = $valor;
            $prealerta->save();

            // 5.0. El tracking pasan sus ambos estados a PDO
            $tracking->ESTADOSINCRONIZADO = 'Prealertado';
            $tracking->ESTADOMBOX = 'Prealertado';
            $tracking->save();
        });
    }
}
