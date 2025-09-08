<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistorialTrackingDetalleResource extends JsonResource
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
            'descripcion' => $this->DESCRIPCION,
            'descripcionModificada' => $this->DESCRIPCIONMODIFICADA,
            'codigoPostal' => $this->CODIGOPOSTAL,
            'paisEstado' => $this->PAISESTADO,
            'ocultado' => $this->OCULTADO,
            'tipo' => $this->TIPO,
            'fecha' => Carbon::parse($this->updated_at)->format('d M Y'), // Ej: 26 Nov 2024
            'hora' => Carbon::parse($this->updated_at)->format('H:i'),     // Ej: 11:00 (hora militar)
            'idTracking' => $this->IDTRACKING,
            'perteneceEstado' => $this->PERTENECEESTADO,
            'actions' => [
                [
                    'descripcion' => 'Editar',
                    'icon' => 'Edit',
                    'actionType' => 'Editar',
                    'isActive' => true,
                    'route' => ''
                ],
                [
                    'descripcion' => !empty($this->DESCRIPCIONMODIFICADA) ? 'Ver Original' : '',
                    'icon' => 'ArrowLeftRight',
                    'actionType' => 'VerOriginal',
                    'isActive' => !empty($this->DESCRIPCIONMODIFICADA),
                    'route' => ''
                ],
                [
                    'descripcion' => $this->OCULTADO ? 'Mostrar' : 'Ocultar',
                    'icon' => $this->OCULTADO ? 'Eye' : 'EyeOff',
                    'actionType' => 'SwitchOcultar',
                    'isActive' => true,
                    'route' => ''
                ],
            ]
        ];
    }
}
