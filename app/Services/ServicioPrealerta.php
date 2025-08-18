<?php

namespace App\Services;

use App\DataTransformers\ModelToIdDescripcionDTO;
use App\Events\EventoRegistroCliente;
use App\Http\Requests\RequestCrearPrealerta;
use App\Models\Cliente;
use App\Models\Direccion;
use App\Models\Prealerta;
use App\Models\Proveedor;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Enum\TipoCardDirecciones;
use App\Models\Enum\TipoDirecciones;
use App\Models\Tracking;
use App\Models\TrackingHistorial;
use Diccionarios;

class ServicioPrealerta
{

    public static function RegistrarPrealerta(RequestCrearPrealerta $request): ?Tracking
    {
        // 1. Obtenemos el tracking mediante el idTracking
        // 2. Revisamos si el tracking tiene un estado = a 'sin preealertar'
        // 3. Si es igual, entonces se prealerta, sino se mantiene el estado que tiene
        // 4. Validamos el idProveedor,
        // 4.1. Si es Aeropost, se llama a AeropostService
        // 4.2. Si es MiLocker, se llama a MiLockerService (donde solo se retorna la prealerta rapido)
        // 4.3 Se pasa el estado a 'prealertado'
        try{

            // 1. Obtenemos el tracking mediante el idTracking
            $tracking = Tracking::where('IDTRACKING', $request->idTracking)->first();

            // 2. Revisamos si el tracking tiene un estado = a 'sin preealertar'
            // 3. Si es igual, entonces se prealerta, sino se mantiene el estado que tiene
            if($tracking->estadoMBox->DESCRIPCION != 'Sin Preealertar'){
                return $tracking; //sin cambios, asi como está xq si no es sin prealertar entonces esta en otro estado
            }

            // 4. Validamos el idProveedor
            $proveedor = Proveedor::find($request->idProveedor);
            $prealerta = new Prealerta();

            // 4.1. Si es Aeropost, se llama a AeropostService
            if($proveedor->NOMBRE == 'Aeropost'){
                $prealerta = ServicioAeropost::RegistrarPrealerta($tracking, $request->valor, $request->descripcion, $request->idProveedor);

            // 4.2. Si es MiLocker, se llama a MiLockerService (donde solo se retorna la prealerta rapido)
            } else if($proveedor->NOMBRE == 'MiLocker'){
                $prealerta = ServicioMiLocker::RegistrarPrealerta($tracking->id, $request->valor, $request->descripcion, $request->idProveedor);
            }


            if (!$prealerta){
                throw new Exception('No se ha podido crear la prealerta');
            }

            // 4.3 Se pasa el estado a 'prealertado'
            $tracking->ESTADOMBOX = 'Prealertado';
            $tracking->save();

            $tracking->load('estadoMBox', 'trackingProveedor.prealerta', 'trackingProveedor');
            return $tracking;

        } catch (Exception $ex) {
            Log::info('[ServicioPrealerta->RegistrarPrealerta] Error: '.$ex->getMessage());
            return null;
        }
    }
}
