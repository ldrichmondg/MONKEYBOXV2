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
        return [
            'id' => $this->id,
            'idTracking' => $this->IDTRACKING,
            'nombreCliente' => $this->direccion->cliente->usuario->nombreCompletoDosApellidos(),
            'descripcion' => empty($this->DESCRIPCION) ? 'N/A' : $this->DESDE,
            'desde' => empty($this->DESDE) ? 'N/A' : $this->DESDE,
            'hasta' => empty($this->HASTA) ? 'N/A' : $this->HASTA,
            'destino' => empty($this->DESTINO) ? 'N/A' : $this->DESTINO,
            'couriers' => $this->COURIER,
            'estatus' => [
                'descripcion' => $this->estadoMBox->DESCRIPCION,
                'colorClass' => $this->estadoMBox->COLORCLASS,
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
