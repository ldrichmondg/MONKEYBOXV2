<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingConsultadosTablePropioResource
{
    // Para un solo tracking
    public static function toArray($tracking)
    {
        $tp = $tracking->trackingProveedor;
        $ultimoHistorial = $tracking->ultimoHistorial;
        $direccionCliente = $tracking->direccion ? ($tracking->direccion->cliente ? $tracking->direccion->cliente->usuario : null) : null;
        $estado = $tracking->estadoMBox;

        return [
            'id' => $tracking->id,
            'trackingMBox' => 'MB' . str_pad($tracking->id, 8, '0', STR_PAD_LEFT),
            'trackingProveedor' => $tp ? $tp->TRACKINGPROVEEDOR : 'N/A',
            'nombreCliente' => $direccionCliente ? $direccionCliente->nombreCompletoDosApellidos() : 'N/A',
            'ultimoHistorialTracking' => $ultimoHistorial ? $ultimoHistorial->DESCRIPCION : null,
            'estatus' => [
                'descripcion' => $estado ? $estado->DESCRIPCION : null,
                'colorClass' => $estado ? $estado->COLORCLASS : null,
            ],
        ];
    }

    // Para una colección de trackings
    public static function toArrayCollection($trackings)
    {
        return $trackings->map(fn($t) => self::toArray($t));
    }
}
