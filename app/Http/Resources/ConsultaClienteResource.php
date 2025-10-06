<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultaClienteResource extends JsonResource
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
            'casillero' => $this->CASILLERO,
            'nombre' => $usuario->NOMBRE,
            'apellidos' => $usuario->APELLIDOS,
            'telefono' => $usuario->TELEFONO,
            'correo' => $usuario->email,
            'cedula' => $usuario->CEDULA,
            'direccionPrincipal' => $this->direccionPrincipal->PAISESTADO,
            'actions' => [
                [
                    'descripcion' => 'Detalle',
                    'icon' => 'Edit',
                    'route' => route('usuario.cliente.detalle.vista', ['id' => $this->id]), // falta lo de poner el #id
                    'actionType' => 'GET',
                    'isActive' => true,
                ],
                [
                    'descripcion' => 'Eliminar',
                    'icon' => 'Trash2',
                    'route' => route('usuario.cliente.eliminar.json'),
                    'actionType' => 'Eliminar',
                    'actionMessage' => 'Estas seguro de eliminar el cliente '.$usuario->NOMBRE. ' ' . $usuario->APELLIDOS.'?',
                    'actionModalTitle' => 'Eliminar Cliente',
                    'isActive' => true,
                ],
            ],
        ];
    }
}
