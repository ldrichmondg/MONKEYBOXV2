<?php

namespace App\Models\Enum;

enum TipoDirecciones: int
{
    case PRINCIPAL = 1;
    case OTRO = 2;

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
