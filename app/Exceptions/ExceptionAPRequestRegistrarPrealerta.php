<?php

namespace App\Exceptions;

use Exception;

class ExceptionAPRequestRegistrarPrealerta extends ExceptionAeropost
{
    public function __construct($message = 'La respuesta no retorno la prealerta', $code = 0, ?Exception $previous = null)
    {
        // internalCode 1004 sera error interno propio para errores de no recibir la prealerta de Aeropost
        parent::__construct($message, 1004, $code, $previous);
    }
}
