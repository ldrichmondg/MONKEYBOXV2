<?php

namespace App\Http\Controllers;

use App\DataTransformers\ModelToIdDescripcionDTO;
use App\Exceptions\EnumCodigosAppError;
use App\Http\Requests\RequestActualizarCliente;
use App\Http\Requests\RequestActualizarDireccion;
use App\Http\Requests\RequestEliminarCliente;
use App\Http\Requests\RequestEliminarUsuario;
use App\Http\Requests\RequestRegistroCliente;
use App\Http\Resources\ClienteDetalleResource;
use App\Http\Resources\ConsultaClienteResource;
use App\Http\Resources\TrackingConsultadosTableResource;
use App\Http\Resources\UsuarioConsultaResource;
use App\Models\Cliente;
use App\Models\Enum\TipoDirecciones;
use App\Models\Provincia;
use App\Models\User;
use App\Services\ServicioCliente;
use App\Services\ServicioUsuario;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ClienteController extends Controller
{
    /**
     * @return Response|RedirectResponse
     */
    public function ConsultaVista(): Response|RedirectResponse{
        try {
            $clientes = Cliente::with('usuario', 'direccionPrincipal')->get();
            return Inertia::render('cliente/consultaCliente',  ['clientes' => (ConsultaClienteResource::collection($clientes))->resolve()]);
        }catch (Exception $e){
            Log::info($e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'Hubo un error al cargar la consulta de clientes.');
        }
    }

    public function ConsultaJson(): JsonResponse
    {
        try {
            $clientes = Cliente::all();
            return response()->json( ['clientes' => ConsultaClienteResource::collection($clientes)->resolve()]);

        } catch (Exception $e) {
            Log::error('[ClienteController->ConsultaVista] error:' . $e);
            return response()->error('Hubo un error al solicitar los clientes');
        }
    }

    /**
     * @param RequestEliminarCliente $request
     * @return JsonResponse
     * @throws QueryException o un ModelNotFoundException
     */
    public function EliminarJson(RequestEliminarCliente $request): JsonResponse{
        try {
            $trackingsEnProceso = ServicioCliente::Eliminar($request->idCliente);

            if (count($trackingsEnProceso) > 0){
                return response()->error('Hay trackings en proceso', 'Error', 500, EnumCodigosAppError::CLIENTE_NO_PUEDE_ELIMINARSE, ['trackingsEnProceso' => (TrackingConsultadosTableResource::collection($trackingsEnProceso)->resolve())]);
            }
            return response()->json(['Exito'],200);
        }catch (Exception $e){
            Log::error('[ClienteController->EliminarJson] error:' . $e);
            return response()->error('Hubo un error al eliminar el cliente');
        }
    }

    /**
     * @return Response|\Illuminate\Http\RedirectResponse
     */
    public function RegistroVista(): Response|RedirectResponse {
        try{

            return Inertia::render('cliente/registroCliente', []);
        }catch (Exception $e){
            return back()->with('error', 'Hubo un error al mostrar la ventana de registro de clientes');
        }
    }

    /**
     * @param RequestRegistroCliente $request
     * @return JsonResponse
     */
    public function RegistroJson(RequestRegistroCliente $request): JsonResponse{
        try{

            $cliente = ServicioCliente::Crear($request);
            return response()->json(['idCliente' => $cliente->id]);
        }catch (Exception $e){
            Log::info($e->getMessage());
            return response()->error('Hubo un error al intentar registrar el cliente');
        }
    }

    /**
     * @param $id
     * @return Response|RedirectResponse
     */
    public function DetalleVista($id): Response|RedirectResponse {
        try{
            $cliente = Cliente::findOrFail($id);
            $provincias = ModelToIdDescripcionDTO::map(Provincia::all());
            $tiposDirecciones = TipoDirecciones::list();
            return Inertia::render('cliente/detalleCliente', ['provincias' => $provincias, 'tiposDirecciones' => $tiposDirecciones, 'cliente' => (new ClienteDetalleResource($cliente))->resolve()]);
        }catch (Exception $e){
            Log::error('[ClienteController->DetalleVista] error:' . $e);
            return back()->with('error', 'Hubo un error al mostrar la ventana de detalle de clientes');
        }
    }

    public function DetalleJson($id): JsonResponse
    {
        try{
            $cliente = Cliente::findOrFail($id);
            return response()->json(['cliente' => (new ClienteDetalleResource($cliente))->resolve()]);
        }catch (Exception $e){
            Log::error('[ClienteController->DetalleVista] error:' . $e);
            return response()->error('Hubo un error al obtener la información del cliente');
        }
    }

    /**
     * @param RequestActualizarCliente $request
     * @return JsonResponse
     */
    public function ActualizaJson(RequestActualizarCliente $request): JsonResponse{

        try{
            ServicioCliente::Actualizar($request);
            return response()->json(['Exito']);
        }catch (Exception $e){

            Log::info($e->getMessage());
            return response()->error('Hubo un error al actualizar el cliente');
        }
    }

    public function ConsultaJsonCombobox(): JsonResponse
    {
        try {
            $clientes = ServicioCliente::ObtenerClientesSimples();

            return response()->json(['clientes' => $clientes]);

        } catch (\Exception $e) {

            Log::error('[ClienteController->ConsultaJsonCombobox] error:' . $e);
            return response()->error('Hubo un error al actualizar el cliente');
        }
    }

}
