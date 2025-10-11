<?php

namespace App\Http\Controllers;

use App\DataTransformers\ModelToIdDescripcionDTO;
use App\Exceptions\ExceptionArchivosDO;
use App\Http\Requests\RequestActualizarEstado;
use App\Http\Requests\RequestActualizarTracking;
use App\Http\Requests\RequestActualizarTrackingEliminarFactura;
use App\Http\Requests\RequestActualizarTrackingSubirFactura;
use App\Http\Requests\RequestTrackingRegistro;
use App\Http\Resources\ClientesComboboxItemsResource;
use App\Http\Resources\TrackingConsultadosTableResource;
use App\Http\Resources\TrackingDetalleResource;
use App\Http\Resources\TrackingSinPreealertarResource;
use App\Models\Cliente;
use App\Models\Direccion;
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
            return Inertia::render('tracking/consultaTracking', ['trackings' => TrackingConsultadosTableResource::collection($trackings)->resolve()]);

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
            $tracking = ServicioTracking::Sincronizar($id);
            $tracking->load(['historialesT', 'imagenes', 'estadoMBox', 'estadoSincronizado']);

            $direcciones = ModelToIdDescripcionDTO::map(Direccion::where('IDCLIENTE', $tracking->direccion->cliente->id)->get());

            return Inertia::render('tracking/detalle/detalleTracking', ['tracking' => (new TrackingDetalleResource($tracking))->resolve(), 'clientes' => ClientesComboboxItemsResource::collection(Cliente::all())->resolve(), 'direcciones' => $direcciones]);

        } catch (ModelNotFoundException $e) {
            Log::info($e->getMessage());
            return back()->with('error', 'Hubo un error al buscar el tracking');
        }
    }

    /**
     * @param RequestActualizarTracking $request
     * @return JsonResponse
     */
    public function ActualizaJson(RequestActualizarTracking $request): JsonResponse
    {
        try {
            Log::info('ENTRAMOS');
            ServicioTracking::ActualizarTracking($request);
            return response()->json(['Exito']);
        } catch (Exception $e) {
            Log::info('[TC, AJ] error: '. $e->getMessage());
            return response()->error('Hubo un error al actualizar el tracking');
        }
    }

    /**
     * @param RequestActualizarEstado $request
     * @return JsonResponse
     */
    public function ActualizaEstado(RequestActualizarEstado $request): JsonResponse{
        try{
            $trackingActualizado = ServicioTracking::ActualizarEstado($request);
            $trackingActualizado->load(['historialesT', 'imagenes', 'estadoMBox', 'estadoSincronizado']);

            return response()->json(['trackingActualizado' => (new TrackingDetalleResource($trackingActualizado))->resolve()]);
        }catch (Exception $e){
            Log::info($e->getMessage());
            return response()->error('Hubo un problema al actualizar los estados del tracking');
        }
    }

    /**
     * @param RequestActualizarTrackingSubirFactura $request
     * @return JsonResponse
     */
    public function SubirFactura(RequestActualizarTrackingSubirFactura $request): JsonResponse{
        try{
            $trackingActualizado = ServicioTracking::SubirFactura($request);
            $trackingActualizado->load(['historialesT', 'imagenes', 'estadoMBox', 'estadoSincronizado']);

            return response()->json(['trackingActualizado' => (new TrackingDetalleResource($trackingActualizado))->resolve()]);
        }catch (Exception $e){
            Log::info('[TC, SF] error: '. $e->getMessage());
            return response()->error('Hubo un error al subir la factura del tracking');
        }
    }

    /**
     * @param RequestActualizarTrackingEliminarFactura $request
     * @return JsonResponse
     */
    public function EliminarFactura(RequestActualizarTrackingEliminarFactura $request): JsonResponse{
        try{
            $trackingActualizado = ServicioTracking::EliminarFactura($request);
            $trackingActualizado->load(['historialesT', 'imagenes', 'estadoMBox', 'estadoSincronizado']);

            return response()->json(['trackingActualizado' => (new TrackingDetalleResource($trackingActualizado))->resolve()]);
        }catch (Exception $e){
            Log::info('[TC, SF] error: '. $e->getMessage());
            return response()->error('Hubo un error al eliminar la factura del tracking');
        }
    }

    public function Sincronizar(RequestActualizarTracking $request){
        // Sincronizar cambios que vengan de ParcelsApp o de Aeropost

        try{
            $tracking = ServicioTracking::Sincronizar($request);
            $tracking->load(['historialesT', 'imagenes', 'estadoMBox', 'estadoSincronizado']);

            return response()->json(['tracking' => (new TrackingDetalleResource($tracking))->resolve()]);
        }catch (Exception $e){
            Log::info('[TC, Sinc] error: '. $e->getMessage());
        }
    }

}
