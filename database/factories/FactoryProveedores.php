<?php

namespace Database\Factories;

use App\Services\Proveedores\Aeropost\ServicioAeropost;
use App\Services\Proveedores\InterfazProveedor;
use Illuminate\Database\Eloquent\Factories\Factory;
use InvalidArgumentException;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class FactoryProveedores
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public static function make(string $nombreProveedor): InterfazProveedor
    {

        return match (strtolower($nombreProveedor)) {
            'aeropost' => new ServicioAeropost(),
            default => throw new InvalidArgumentException("Proveedor no soportado: $nombreProveedor"),
        };
    }
}
