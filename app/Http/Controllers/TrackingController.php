<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestTrackingRegistro;
use App\Http\Resources\TrackingConsultadosTableResource;
use App\Http\Resources\TrackingDetalleResource;
use App\Http\Resources\TrackingSinPreealertarResource;
use App\Models\Tracking;
use App\Services\ServicioCliente;
use App\Services\ServicioTracking;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

// Nomenclatura a usar:
// SubmoduloAccion (recursoID)

class TrackingController
{
    /**
     * @return Response|RedirectResponse
     * Possible throws:
     * @throws QueryException
     */
    public function ConsultaVista(): Response|RedirectResponse
    {
        try {
            $trackings = Tracking::all();
            Log::info($trackings);
            return Inertia::render('tracking/consultaTracking', ['trackings' =>  TrackingConsultadosTableResource::collection($trackings)->resolve()]);

        } catch (Exception $e) {
            Log::error('[TrackingController->ConsultaVista] error:' . $e);
            return redirect()->route('tracking.consulta.vista')
                ->with('error', 'Hubo un error al cargar la ventana.');
        }
    }

    /**
     * @return Response|RedirectResponse
     * Possible throws:
     * @throws QueryException
     */
    public function RegistroMasivoVista(): Response|RedirectResponse
    {
        try {
            $clientes = ServicioCliente::ObtenerClientesSimples();

            return Inertia::render('tracking/registroMasivo',
                ['clientes' => $clientes]
            );

        } catch (\Exception $e) {

            Log::error('[TrackingController->RegistroMasivoVista] error:' . $e);
            return redirect()->back()
                ->with('error', 'Hubo un error al cargar la ventana.');

        }
    }

    /**
     * @param RequestTrackingRegistro $request
     * @return JsonResponse
     */
    public function RegistroJson(RequestTrackingRegistro $request): JsonResponse
    {
        try {

            $tracking = ServicioTracking::ObtenerORegistrarTracking($request);

            return response()->json(new TrackingSinPreealertarResource($tracking));

        } catch (\Exception $e) {

            Log::error('[TrackingController->Registro] error:' . $e);

            return response()->json([
                'status' => 'error',
                'message' => 'Algo ocurrió al registrar el tracking. Ver el Log',
            ], 500); // 500 = Internal Server Error
        }
    }

    /**
     * @param int $id
     * @return Response|RedirectResponse
     * @throws ModelNotFoundException
     */
    public function Detalle(int $id): Response|RedirectResponse // tengo que poner un request para verificar que el id del tracking existe
    {
        try {
            $tracking = Tracking::findOrFail($id);
            return Inertia::render('tracking/detalleTracking', ['tracking' => (new TrackingDetalleResource($tracking))->resolve()]);

        } catch (ModelNotFoundException $e) {

            return back()->with('error', 'Hubo un error al buscar el tracking');
        }
    }
}
