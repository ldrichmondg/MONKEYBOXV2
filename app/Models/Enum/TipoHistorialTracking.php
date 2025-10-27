<?php

namespace App\Models\Enum;

enum TipoHistorialTracking: int
{
    case API = 1;
    case SISTEMA = 2;

    case AEROPOST = 3;

    public function descripcion(): string
    {
        return $this->name;
    }

    // Método que devuelve el id (el value del enum)
    public function id(): int
    {
        return $this->value;
    }

    // Método helper para mapear todos
    public static function list(): array
    {
        return array_map(fn ($case) => [
            'id' => $case->id(),
            'descripcion' => $case->descripcion(),
        ], self::cases());
    }
}
