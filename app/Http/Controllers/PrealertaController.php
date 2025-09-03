<?php

namespace App\Http\Controllers;

use App\Exceptions\ExceptionAeropost;
use App\Http\Requests\RequestCrearPrealerta;
use App\Http\Resources\TrackingRecienPrealertadoConProveedorResource;
use App\Services\ServicioPrealerta;
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
}
