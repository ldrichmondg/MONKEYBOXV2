<?php

namespace App\DataTransformers;

use Illuminate\Support\Collection;

class ModelToIdDescripcionDTO
{
    public static function map(Collection|array $collection): array
    {
        // la funcion transformara los datos con las siguientes reglas:
        // 1. los campos tienen que venir en mayuscula
        $items = collect($collection);

        return $items->map(function ($item) {
            $descripcion = $item->DESCRIPCION
                ?? $item->NOMBRE
                ?? 'Sin descripción';

            // Caso de que se use cliente/usuario
            if (isset($item->APELLIDOS) && isset($item->NOMBRE)) {
                $descripcion = $item->NOMBRE.' '.$item->APELLIDOS;
            }

            return [
                'id' => $item->id,
                'descripcion' => $descripcion,
            ];
        })->toArray();
    }
}
