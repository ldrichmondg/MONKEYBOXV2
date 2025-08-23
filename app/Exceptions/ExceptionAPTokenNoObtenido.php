<?php

namespace App\Exceptions;

use Exception;

class ExceptionAPTokenNoObtenido extends ExceptionAeropost
{
    public function __construct($message = 'No se pudo obtener el token de Aeropost', $code = 0, ?Exception $previous = null)
    {
        // internalCode 1001 sera error interno propio para errores de token
        parent::__construct($message, 1001, $code, $previous);
    }
}
