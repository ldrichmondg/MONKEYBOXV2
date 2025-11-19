<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingConsultadosTableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tp = $this->trackingProveedor; // ya viene eager loaded
        $prealerta = $tp ? $tp->prealerta : null;
        $ultimoHistorial = $this->ultimoHistorial; // eager loaded
        $direccionCliente = $this->direccion ? ($this->direccion->cliente ? $this->direccion->cliente->usuario : null) : null;
        $estado = $this->estadoMBox;

        return [
            'id' => $this->id,
            'trackingMBox' => $this->trackingMBox(),
            'trackingProveedor' => $tp ? $tp->TRACKINGPROVEEDOR : 'N/A',
            'idTracking' => $this->IDTRACKING,
            'nombreCliente' => $direccionCliente ? $direccionCliente->nombreCompletoDosApellidos() : 'N/A',
            'descripcion' => $prealerta ? $prealerta->DESCRIPCION : 'N/A',
            'ultimaActualizacion' => ($this->fechaUltimaTrackingRelacionado() ?? $this->updated_at)->format('d/m/y H:i'),
            'ultimoHistorialTracking' => $ultimoHistorial ? $ultimoHistorial->DESCRIPCION : null,
            'couriers' => $this->COURIER,
            'estatus' => [
                'descripcion' => $estado ? $estado->DESCRIPCION : null,
                'colorClass' => $estado ? $estado->COLORCLASS : null,
            ],
            'actions' => [
                [
                    'descripcion' => 'Detalle',
                    'icon' => 'Edit',
                    'route' => route('usuario.tracking.detalle.vista', ['id' => $this->id]), //$this->id falta lo de poner el #id
                    'actionType' => 'GET',
                    'isActive' => true,
                ],
                [
                    'descripcion' => 'Eliminar',
                    'icon' => 'Trash2',
                    'route' => route('dashboard'),
                    'actionType' => 'Eliminar',
                    'actionMessage' => 'Estas seguro de eliminar el tracking'.$this->IDTRACKING.'?',
                    'actionModalTitle' => 'Eliminar Tracking',
                    'isActive' => true,
                ],
            ]
        ];
    }
}
