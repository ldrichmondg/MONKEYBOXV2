<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestConsultaDirecciones;
use App\Http\Resources\DireccionResource;
use App\Models\Direccion;
use App\Services\ServicioDireccion;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DireccionController extends Controller
{
    /**
     * @param RequestConsultaDirecciones $request
     * @return JsonResponse
     * @throws QueryException
     */
    public function ConsultaJSON(RequestConsultaDirecciones $request): JsonResponse{

        try{

            $direcciones = ServicioDireccion::ObtenerDirecciones($request->idCliente);
            return response()->json(['direcciones' => DireccionResource::collection($direcciones)->resolve()]);
        } catch (\Exception $e){

            Log::error('[DireccionController->ConsultaJSON] error:'.$e);
            return response()->error('Hubo un error al obtener las direcciones del cliente');

        }
    }
}
