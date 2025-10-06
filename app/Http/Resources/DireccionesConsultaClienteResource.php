<?php

namespace App\Http\Resources;

use App\Models\Enum\TipoDirecciones;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DireccionesConsultaClienteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $nombreTipo = TipoDirecciones::from($this->TIPO)->name;
        return [
            'id' => $this->id,
            'direccion' => $this->DIRECCION,
            'tipo' => $this->TIPO,
            'idCliente' => -1, // no es necesario por el momento,
            'codigoPostal' => $this->CODIGOPOSTAL,
            'paisEstado' => $this->PAISESTADO,
            'linkWaze' => $this->LINKWAZE,
            'tipoStatus' => [
                'descripcion' => $nombreTipo,
                'colorClass' => $nombreTipo == 'PRINCIPAL'
                    ? 'bg-transparent border-green-400 text-green-400'
                    : 'bg-transparent border-blue-400 text-blue-400'
            ],
            'actions' => [
                [
                    'descripcion' => 'Detalle',
                    'icon' => 'Edit',
                    'route' => '',
                    'actionType' => '',
                    'isActive' => true,
                ],
                [
                    'descripcion' => 'Eliminar',
                    'icon' => 'Trash2',
                    'route' => '',
                    'actionType' => '',
                    'actionMessage' => '',
                    'actionModalTitle' => '',
                    'isActive' => true,
                ],
            ]
        ];
    }
}
