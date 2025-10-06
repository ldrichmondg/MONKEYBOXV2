<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteDetalleResource extends JsonResource
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
            'apellidos' => $usuario->APELLIDOS,
            'empresa' =>  $usuario->EMPRESA,
            'telefono' => $usuario->TELEFONO,
            'correo' =>  $usuario->email,
            'cedula'  => $usuario->CEDULA,

            'casillero' => $this->CASILLERO,
            'fechaNacimiento' => $this->FECHANACIMIENTO,
            'direcciones' => DireccionesConsultaClienteResource::collection($this->direcciones)->resolve(),
        ];
    }
}
