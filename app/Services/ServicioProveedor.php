<?php

namespace App\Services;

use App\DataTransformers\ModelToIdDescripcionDTO;
use App\Events\EventoRegistroCliente;
use App\Models\Cliente;
use App\Models\Direccion;
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

class ServicioProveedor
{

    /*public static function ObtenerProveedores(): ?Collection
    {

        try{

            Proveedor::all();

        } catch (Exception $ex) {
            Log::error('[ServicioProveedor->ObtenerProveedores] Error: '.$ex->getMessage());
            return null;
        }
    }*/
}
