<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingSinPreealertarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        if (is_null($this->resource)) {
            return [
                'trackingCompleto' => false
            ];
        }

        return [
            'id' => $this->id,
            'idTracking' => $this->IDTRACKING,
            'nombreCliente' => $this->direccion->cliente->usuario->nombreCompletoDosApellidos(),
            'idCliente' => $this->direccion->cliente->id,
            'desde' => empty($this->DESDE) ? 'N/A':  $this->DESDE,
            'hasta' => empty($this->HASTA) ? 'N/A':  $this->HASTA,
            'couriers' => $this->COURIER,
            'estatus' => [
                'descripcion' => $this->estadoMBox->DESCRIPCION,
                'colorClass' => $this->estadoMBox->COLORCLASS
            ],
            'acciones' => [
                [
                    'descripcion' => 'Detalle',
                    'icon' => 'Edit',
                    'route' => route('usuario.tracking.detalle.vista'), //falta lo de poner el #id
                    'actionType' => 'GET',
                    'isActive' => true
                ],
                [
                    'descripcion' => 'Eliminar',
                    'icon' => 'Trash2',
                    'route' => route('dashboard'),
                    'actionType' => 'Eliminar',
                    'actionMessage' => 'Estas seguro de eliminar el tracking'. $this->IDTRACKING .'?',
                    'actionModalTitle' => 'Eliminar Tracking',
                    'isActive' => true
                ]
            ],
            'trackingCompleto' => true,
        ];
    }
}
