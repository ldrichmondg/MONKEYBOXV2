<?php

namespace App\Http\Controllers;

use App\DataTransformers\ModelToIdDescripcionDTO;
use App\Http\Requests\RequestActualizarDireccion;
use App\Http\Requests\RequestConsultaDirecciones;
use App\Http\Requests\RequestDetalleCodigoPostal;
use App\Http\Requests\RequestObtenerCantones;
use App\Http\Requests\RequestObtenerCodigoPostal;
use App\Http\Requests\RequestObtenerDistritos;
use App\Http\Resources\DireccionResource;
use App\Http\Resources\TrackingConsultadosTableResource;
use App\Models\Canton;
use App\Models\Direccion;
use App\Models\Distrito;
use App\Models\Provincia;
use App\Services\ServicioDireccion;
use Exception;
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

    /**
     * @param RequestObtenerCantones $request
     * @return JsonResponse
     */
    public function ConsultaCantonesJson(RequestObtenerCantones $request): JsonResponse{
        try{
            $cantones = ModelToIdDescripcionDTO::map(Canton::where('IDPROVINCIA', $request->id)->get());
            return response()->json(['cantones' => $cantones]);
        }catch (Exception $e){
            return response()->error('Hubo un error al obtener las cantones');
        }
    }

    /**
     * @param RequestObtenerDistritos $request
     * @return JsonResponse
     */
    public function ConsultaDistritoJson(RequestObtenerDistritos $request): JsonResponse{
        try{
            $distritos = ModelToIdDescripcionDTO::map(Distrito::where('IDCANTON', $request->id)->get());
            return response()->json(['distritos' => $distritos]);
        }catch (Exception $e){
            return response()->error('Hubo un error al obtener los distritos');
        }
    }

    /**
     * @param RequestObtenerCodigoPostal $request
     * @return JsonResponse
     */
    public function ConsultaCodigoPostalJson(RequestObtenerCodigoPostal $request): JsonResponse{
        try{
            $distrito = Distrito::findOrFail($request->id);
            return response()->json(['codigoPostal' => $distrito->CODIGOPOSTAL]);
        }catch (Exception $e){
            return response()->error('Hubo un error al obtener el codigo postal');
        }
    }

    /**
     * @param RequestDetalleCodigoPostal $request
     * @return JsonResponse
     */
    public function DetalleCodigoPostalJson(RequestDetalleCodigoPostal $request): JsonResponse{
        try{
            $distrito = Distrito::where('CODIGOPOSTAL',$request->codigopostal)->firstOrFail();
            $canton = $distrito->canton;
            $provincia = $canton->provincia;
            return response()->json(['distritoId' => $distrito->id, 'cantonId' => $canton->id, 'provinciaId' => $provincia->id]);
        }catch (Exception $e){
            Log::info($e->getMessage());
            return response()->error('Hubo un error al obtener la provincia, canton y distrito del codigo postal');
        }
    }

    /**
     * @param RequestActualizarDireccion $request
     * @return JsonResponse
     */
    public function ConsultaTrackings(RequestActualizarDireccion $request): JsonResponse{
        try{
            $trackings = ServicioDireccion::ConsultaTrackings($request->idDireccion);
            return response()->json(['trackingsDireccion' => TrackingConsultadosTableResource::collection($trackings)->resolve()]);
        }catch (Exception $e){
            return response()->error('Hubo un error al intentar obtener los trackings de la direccion solicitada');
        }
    }
}
