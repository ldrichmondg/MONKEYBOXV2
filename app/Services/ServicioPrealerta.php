<?php

namespace App\Services;

use App\Http\Requests\RequestCrearPrealerta;
use App\Models\Prealerta;
use App\Models\Proveedor;
use App\Models\Tracking;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ServicioPrealerta
{
    public static function RegistrarPrealerta(RequestCrearPrealerta $request): Tracking
    {
        // 1. Obtenemos el tracking mediante el idTracking
        // 2. Revisamos si el tracking tiene un estado = a 'sin preealertar'
        // 3. Si es igual, entonces se prealerta, sino se mantiene el estado que tiene
        // 4. Validamos el idProveedor,
        // 4.1. Si es Aeropost, se llama a AeropostService
        // 4.2. Si es MiLocker, se llama a MiLockerService (donde solo se retorna la prealerta rapido)
        // 4.3 Se pasa el estado a 'prealertado'

        // - Observaciones:
        // No se hace try-catch de QueryException porque se puede hacer un transaction para que haga rollback si algo no se hizo correctamente en la BD
        return DB::transaction(function () use ($request) {

            // 1. Obtenemos el tracking mediante el idTracking
            $tracking = Tracking::where('IDTRACKING', $request->idTracking)->firstOrFail();

            // 2. Revisamos si el tracking tiene un estado = a 'sin preealertar'
            // 3. Si es igual, entonces se prealerta, sino se mantiene el estado que tiene
            if ($tracking->estadoMBox->DESCRIPCION != 'Sin Preealertar') {
                return $tracking; // sin cambios, asi como está xq si no es sin prealertar entonces esta en otro estado
            }

            // 4. Validamos el idProveedor
            $proveedor = Proveedor::find($request->idProveedor);

            // 4.1. Si es Aeropost, se llama a AeropostService
            if ($proveedor->NOMBRE == 'Aeropost') {
                ServicioAeropost::RegistrarPrealerta($tracking, $request->valor, $request->descripcion, $request->idProveedor);

                // 4.2. Si es MiLocker, se llama a MiLockerService (donde solo se retorna la prealerta rapido)
            } elseif ($proveedor->NOMBRE == 'MiLocker') {
                ServicioMiLocker::RegistrarPrealerta($tracking->id, $request->valor, $request->descripcion, $request->idProveedor);
            }

            // 4.3 Se pasa el estado a 'prealertado'
            $tracking->ESTADOMBOX = 'Prealertado';
            $tracking->save();

            $tracking->load('estadoMBox', 'trackingProveedor.prealerta', 'trackingProveedor');

            return $tracking;
        });
    }
}
