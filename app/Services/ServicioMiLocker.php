<?php

namespace App\Services;

use App\Models\Prealerta;
use App\Models\TrackingProveedor;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ServicioMiLocker
{
    /**
     * @param int $idTracking
     * @param float $valor
     * @param string $descripcion
     * @param int $idProveedor
     * @return Prealerta
     * @throws ModelNotFoundException
     * @throws QueryException
     */
    public static function RegistrarPrealerta(int $idTracking, float $valor, string $descripcion, int $idProveedor): Prealerta
    {
        // - Verificar si ya existe un trackingProveedor con ese idTracking
        // - Si existe retornar la prealerta
        // 1. Crear el tracking proveedor
        // 2. Crear la prealerta

        Log::info('ENTRADA V1');
        // - Verificar si ya existe un trackingProveedor con ese idTracking
        $trackingProveedor = TrackingProveedor::where('IDTRACKING', $idTracking)->first();

        // - Si existe retornarlo
        if ($trackingProveedor) {
            return $trackingProveedor->prealerta;
        }

        // 1. Crear el tracking proveedor
        $trackingProveedor = new TrackingProveedor();
        $trackingProveedor->IDTRACKING = $idTracking;
        $trackingProveedor->IDPROVEEDOR = $idProveedor;
        $trackingProveedor->save();

        // 2. Crear la prealerta
        $prealerta = new Prealerta();
        $prealerta->DESCRIPCION = $descripcion;
        $prealerta->VALOR = $valor;
        $prealerta->IDCOURIER = 0; // 0 como default porque aca se ponen los couriers de los proveedores
        $prealerta->NOMBRETIENDA = 'TIENDA DE';
        $prealerta->IDTRACKINGPROVEEDOR = $trackingProveedor->id;
        $prealerta->save();

        Log::info('Prealerta de MiLocker: '.json_encode($prealerta));

        return $prealerta;
    }
}
