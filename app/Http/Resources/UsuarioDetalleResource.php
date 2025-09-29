<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioDetalleResource extends JsonResource
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
            'nombre' => $this->NOMBRE,
            'apellidos' => $this->APELLIDOS,
            'telefono' => $this->TELEFONO,
            'correo' => $this->email,
            'cedula' => $this->CEDULA,
            'empresa' => $this->EMPRESA,
        ];
    }
}
