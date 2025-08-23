<?php

namespace App\Exceptions;

use Exception;

class ExceptionAPCouriersNoObtenidos extends ExceptionAeropost
{
    public function __construct($message = 'No se encontraron los couriers de Aeropost', $code = 0, ?Exception $previous = null)
    {
        // internalCode 1002 sera error interno propio para errores de no encontrar couriers
        parent::__construct($message, 1002, $code, $previous);
    }
}
