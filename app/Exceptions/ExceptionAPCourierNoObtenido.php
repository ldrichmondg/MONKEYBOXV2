<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class ExceptionAPCourierNoObtenido extends ExceptionAeropost
{
    public function __construct($message = "No se encontro el courier seleccionado de Aeropost", $code = 0, Exception $previous = null)
    {
        //internalCode 1003 sera error interno propio para errores de no encontrar un courier en especifico
        //Log::info('['.$ubicacion.'] error: '. $message);
        parent::__construct($message, 1003, $code, $previous);
    }
}
