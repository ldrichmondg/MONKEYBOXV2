<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestTrackingRegistro;
use App\Http\Resources\TrackingSinPreealertarResource;
use App\Services\ServicioCliente;
use App\Services\ServicioTracking;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

// Nomenclatura a usar:
// SubmoduloAccion (recursoID)

class TrackingController
{
    public function ConsultaVista(): Response
    {

        return Inertia::render('tracking/consultaTracking', []);
    }

    public function RegistroMasivoVista(): Response
    {

        $clientes = ServicioCliente::ObtenerClientesSimples();

        return Inertia::render('tracking/registroMasivo',
            ['clientes' => $clientes]
        );
    }

    public function RegistroJson(RequestTrackingRegistro $request): JsonResponse
    {

        try {

            $tracking = ServicioTracking::ObtenerORegistrarTracking($request);

            return response()->json(new TrackingSinPreealertarResource($tracking));

        } catch (\Exception $e) {

            Log::error('[TrackingController->Registro] error:'.$e);

            return response()->json([
                'status' => 'error',
                'message' => 'Algo ocurrió al registrar el tracking. Ver el Log',
            ], 500); // 500 = Internal Server Error
        }
    }

    public function Detalle(): Response // tengo que poner un request para verificar que el id del tracking existe
    {return Inertia::render('tracking/detalleTracking', []);
    }
}
