<?php

namespace App\Http\Controllers;

use App\Http\Requests\EstadoMBoxConsultaVariosRequest;
use App\Services\ServicioEstadoMBox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EstadoMBoxController extends Controller
{
    public function DetallesJson(EstadoMBoxConsultaVariosRequest $request): JsonResponse
    {
        try {

            $estados = ServicioEstadoMBox::ObtenerEstadosMBox($request->estadosMBox);

            if ($estados == null) {
                throw new \Exception('No se encontraron estados con las descripciones pasadas');
            }

            return response()->json($estados);

        } catch (\Exception $e) {
            Log::error('[EstadoMBoxController->DetallesJson] error:' . $e);

            return response()->json([
                'status' => 'error',
                'message' => 'Algo ocurrió al buscar estadosMBox. Ver el Log',
            ], 500); // 500 = Internal Server Error
        }
    }

}

