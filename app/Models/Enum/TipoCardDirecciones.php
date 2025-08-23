<?php

namespace App\Models\Enum;

enum TipoCardDirecciones: int
{
    case CLIENTES = 1;
    case TRANSITO = 2;
    case ENTREGADOS = 3;
}
