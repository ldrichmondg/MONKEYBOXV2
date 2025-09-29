<?php

namespace App\Http\Controllers;

use App\DataTransformers\ModelToIdDescripcionDTO;
use App\Http\Requests\RequestActualizarUsuario;
use App\Http\Requests\RequestEliminarUsuario;
use App\Http\Requests\RequestRegistrarUsuario;
use App\Http\Resources\ClientesComboboxItemsResource;
use App\Http\Resources\TrackingDetalleResource;
use App\Http\Resources\UsuarioConsultaResource;
use App\Http\Resources\UsuarioDetalleResource;
use App\Models\Cliente;
use App\Models\Direccion;
use App\Models\Tracking;
use App\Models\User;
use App\Services\ServicioUsuario;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class UsuarioController extends Controller
{
    public function ConsultaVista(): Response|RedirectResponse
    {
        try {
            $usuarios = User::where('IDPERFIL', '!=', 3)->get();
            return Inertia::render('usuario/consultaUsuario', ['usuarios' => UsuarioConsultaResource::collection($usuarios)->resolve()]);

        } catch (Exception $e) {
            Log::error('[TrackingController->ConsultaVista] error:' . $e);
            return redirect()->route('welcome')
                ->with('error', 'Hubo un error al cargar la consulta de usuarios.');
        }
    }

    public function ConsultaJson(): JsonResponse
    {
        try {
            $usuarios = User::where('IDPERFIL', '!=', 3)->get();
            return response()->json( ['usuarios' => UsuarioConsultaResource::collection($usuarios)->resolve()]);

        } catch (Exception $e) {
            Log::error('[TrackingController->ConsultaVista] error:' . $e);
            return response()->error('Hubo un error al solicitar los usuarios');
        }
    }

    /**
     * @param RequestEliminarUsuario $request
     * @return JsonResponse
     * @throws QueryException o un ModelNotFoundException
     */
    public function EliminarJson(RequestEliminarUsuario $request): JsonResponse{
        try {
            ServicioUsuario::Eliminar($request->idUsuario);
            return response()->json(['Exito'],200);
        }catch (Exception $e){
            Log::error('[UsuarioController->EliminarJson] error:' . $e);
            return response()->error('Hubo un error al eliminar usuario');
        }
    }
    /**
     * @param int $id
     * @return Response|RedirectResponse
     * @throws ModelNotFoundException
     */
    public function DetalleVista(int $id): Response|RedirectResponse // tengo que poner un request para verificar que el id del tracking existe
    {
        try {
            $usuario = User::findOrFail($id);
            return Inertia::render('usuario/detalleUsuario', ['usuario' => (new UsuarioDetalleResource($usuario))->resolve()]);

        } catch (ModelNotFoundException $e) {
            return back()->with('error', 'Hubo un error al buscar el usuario');
        }
    }

    /**
     * @param RequestActualizarUsuario $request
     * @return JsonResponse
     * @throws ModelNotFoundException
     */
    public function ActualizaJson(RequestActualizarUsuario $request): JsonResponse{

        try{
            ServicioUsuario::Actualizar($request->id, $request->nombre, $request->apellidos, $request->telefono, $request->correo, $request->empresa);
            return response()->json(['Exito'],200);
        }catch (Exception $e){
            return response()->error('Hubo un error al actualizar usuario');
        }
    }

    /**
     * @return Response|RedirectResponse
     */
    public function RegistroVista(): Response|RedirectResponse {
        try{
            return Inertia::render('usuario/registroUsuario');
        }catch (Exception $e){
            return back()->with('error', 'Hubo un error al mostrar la ventana de registro del usuario');
        }
    }

    /**
     * @param RequestActualizarUsuario $request
     * @return JsonResponse
     */
    public function RegistroJson(RequestRegistrarUsuario $request): JsonResponse{

        try{
            ServicioUsuario::Actualizar($request->id, $request->nombre, $request->apellidos, $request->telefono, $request->correo, $request->empresa);
            return response()->json(['Exito'],200);
        }catch (Exception $e){
            return response()->error('Hubo un error al actualizar usuario');
        }
    }
}
