<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetalleClienteTrackingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $usuario = $this->usuario;
        return [
            'id' => $this->id,
            'nombre' => $usuario->NOMBRE,
            'telefono' => $usuario->TELEFONO,
        ];
    }
}
