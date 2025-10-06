<?php

namespace App\Models\Enum;

enum TipoPerfiles: int
{
    case Administrador = 1;
    case Trabajador = 2;
    case Clientes = 3;
}
