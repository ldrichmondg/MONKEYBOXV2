<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingConsultadosTablePropioResource
{
    // Para un solo tracking
    public static function toArray($tracking)
    {
        $tp = $tracking->trackingProveedor; // ya viene eager loaded
        $prealerta = $tp ? $tp->prealerta : null;
        $ultimoHistorial = $tracking->ultimoHistorial; // eager loaded
        $direccionCliente = $tracking->direccion ? ($tracking->direccion->cliente ? $tracking->direccion->cliente->usuario : null) : null;
        $estado = $tracking->estadoMBox;

        return [
            'id' => $tracking->id,
            'trackingMBox' => $tracking->trackingMBox(),
            'trackingProveedor' => $tp ? $tp->TRACKINGPROVEEDOR : 'N/A',
            'idTracking' => $tracking->IDTRACKING,
            'nombreCliente' => $direccionCliente ? $direccionCliente->nombreCompletoDosApellidos() : 'N/A',
            'descripcion' => $prealerta ? $prealerta->DESCRIPCION : 'N/A',
            'ultimaActualizacion' => ($tracking->fechaUltimaTrackingRelacionado() ?? $tracking->updated_at)->format('d/m/y H:i'),
            'ultimoHistorialTracking' => $ultimoHistorial ? $ultimoHistorial->DESCRIPCION : null,
            'couriers' => $tracking->COURIER,
            'estatus' => [
                'descripcion' => $estado ? $estado->DESCRIPCION : null,
                'colorClass' => $estado ? $estado->COLORCLASS : null,
            ],
            'actions' => [
                [
                    'descripcion' => 'Detalle',
                    'icon' => 'Edit',
                    'route' => route('usuario.tracking.detalle.vista', ['id' => $tracking->id]),
                    'actionType' => 'GET',
                    'isActive' => true,
                ],
                [
                    'descripcion' => 'Eliminar',
                    'icon' => 'Trash2',
                    'route' => route('dashboard'),
                    'actionType' => 'Eliminar',
                    'actionMessage' => 'Estas seguro de eliminar el tracking'.$tracking->IDTRACKING.'?',
                    'actionModalTitle' => 'Eliminar Tracking',
                    'isActive' => true,
                ],
            ]
        ];
    }

    // Para una colección de trackings
    public static function toArrayCollection($trackings)
    {
        return $trackings->map(fn($t) => self::toArray($t));
    }
}
