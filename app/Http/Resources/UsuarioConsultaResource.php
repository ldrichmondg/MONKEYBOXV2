<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioConsultaResource extends JsonResource
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
            'actions' => [
                [
                    'descripcion' => 'Detalle',
                    'icon' => 'Edit',
                    'route' => route('usuario.usuario.detalle.vista', ['id' => $this->id]), // falta lo de poner el #id
                    'actionType' => 'GET',
                    'isActive' => true,
                ],
                [
                    'descripcion' => 'Eliminar',
                    'icon' => 'Trash2',
                    'route' => route('usuario.usuario.eliminar.json'),
                    'actionType' => 'Eliminar',
                    'actionMessage' => 'Estas seguro de eliminar el usuario '.$this->NOMBRE. ' ' . $this->APELLIDOS.'?',
                    'actionModalTitle' => 'Eliminar Usuario',
                    'isActive' => true,
                ],
            ],
        ];
    }
}
