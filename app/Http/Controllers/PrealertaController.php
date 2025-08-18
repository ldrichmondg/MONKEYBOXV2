<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestCrearPrealerta;
use App\Http\Resources\TrackingRecienPrealertadoConProveedorResource;
use App\Http\Resources\TrackingSinPreealertarResource;
use App\Services\ServicioPrealerta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrealertaController extends Controller
{
    public function RegistroJson(RequestCrearPrealerta $request) : JsonResponse {

        try{

            $tracking = ServicioPrealerta::RegistrarPrealerta($request);

            return response()->json(new TrackingRecienPrealertadoConProveedorResource($tracking));

        } catch (\Exception $e) {

            Log::error('[PrealertaController->RegistroJson] error:' . $e);

            return response()->json([
                'status' => 'error',
                'message' => 'Algo ocurrió al registrar la prealerta. Ver el Log',
            ], 500); // 500 = Internal Server Error
        }
    }
}
