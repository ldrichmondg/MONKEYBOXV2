<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DireccionResource extends JsonResource
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
            'direccion' => $this->DIRECCION,
            'tipo' => $this->TIPO,
            'idCliente' => $this->IDCLIENTE,
            'paisEstado' => $this->PAISESTADO,
            'linkWaze' => $this->LINKWAZE
            // poner en un futuro si se desea la relacion de cliente
        ];
    }
}
