<?php

namespace App\Http\Controllers;

use App\Exceptions\EnumCodigosAppError;
use App\Exceptions\ExceptionAeropost;
use App\Exceptions\ExceptionAPCourierNoObtenido;
use App\Exceptions\ExceptionAPCouriersNoObtenidos;
use App\Exceptions\ExceptionAPRequestActualizarPrealerta;
use App\Exceptions\ExceptionAPRequestEliminarPrealerta;
use App\Exceptions\ExceptionAPRequestRegistrarPrealerta;
use App\Exceptions\ExceptionAPTokenNoObtenido;
use App\Exceptions\ExceptionPrealertaNotFound;
use App\Exceptions\ExceptionTrackingProveedorNotFound;
use App\Http\Requests\RequestCrearPrealerta;
use App\Http\Requests\RequestEliminarPrealerta;
use App\Http\Resources\TrackingRecienPrealertadoConProveedorResource;
use App\Services\ServicioPrealerta;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PrealertaController extends Controller
{
    /**
     * @param RequestCrearPrealerta $request
     * @return JsonResponse
     */
    public function RegistroJson(RequestCrearPrealerta $request): JsonResponse
    {

        try {
            $tracking = ServicioPrealerta::RegistrarPrealerta($request);

            return response()->json(new TrackingRecienPrealertadoConProveedorResource($tracking));

        } catch (ExceptionAeropost $e) {

            Log::error('[PrealertaController->RegistroJson] errorAP:'.$e);
            return response()->error('Hubo un error al comunicarse con la app de Aeropost.');

        } catch(\Exception $e) {
            Log::error('[PrealertaController->RegistroJson] errorE:'.$e);
            return response()->error('Hubo un error al crear la prealerta.');
        }
    }

    /**
     * @param RequestCrearPrealerta $request
     * @return JsonResponse
     *
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
    public function ActualizaJson(RequestCrearPrealerta $request): JsonResponse{

        try{
            ServicioPrealerta::ActualizarPrealerta($request->idTracking, $request->descripcion, $request->valor, $request->idProveedor);
            return response()->json('Exito', 200);
        }
        catch(QueryException $e){
            return response()->error('Hubo un error al actualizar la prealerta.', 'Error al actualizar prealerta', 500, EnumCodigosAppError::ERROR_INTERNO);
        }
        catch(ConnectionException $e){
            return response()->error('Hubo un error al comunicarse con la app de Aeropost.', 'Error al comunicarse con app de Aeropost', 500, EnumCodigosAppError::ERROR_AEROPOST);
        }
    }

    /**
     * @param RequestEliminarPrealerta $request
     * @return JsonResponse
     * @throws ExceptionPrealertaNotFound
     * @throws ExceptionTrackingProveedorNotFound
     * @throws QueryException
     * @throws ExceptionAPRequestEliminarPrealerta
     */
    public function EliminarJson(RequestEliminarPrealerta $request): JsonResponse {
        try{
            ServicioPrealerta::EliminarPrealerta($request->idTracking);
            return response()->json('Exito', 200);
        } catch( QueryException $e){
            return response()->error('Error al eliminar prealerta');
        }
    }
}
