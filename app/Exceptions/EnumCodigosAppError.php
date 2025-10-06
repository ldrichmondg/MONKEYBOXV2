<?php

namespace App\Exceptions;

enum EnumCodigosAppError: string{
    case ERROR_AEROPOST = 'ERROR_AEROPOST';
    case ERROR_INTERNO = 'ERROR_INTERNO';
    case PREALERTA_NOT_FOUND = 'PREALERTA_NOT_FOUND';

    case CLIENTE_NO_PUEDE_ELIMINARSE = 'CLIENTE_NO_PUEDE_ELIMINARSE';
}
