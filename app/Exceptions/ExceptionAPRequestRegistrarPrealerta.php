<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExceptionAPRequestRegistrarPrealerta extends ExceptionAeropost
{
    public function __construct($message = 'La respuesta no retorno la prealerta', $code = 0, ?Exception $previous = null)
    {
        // internalCode 1004 sera error interno propio para errores de no recibir la prealerta de Aeropost
        parent::__construct($message, 1004, $code, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse|bool
    {
        if($request->expectsJson()){
            return response()->error('Hubo un error al registrar la prealerta en Aeropost', 'Error al crear la prealerta en Aeropost', 500, EnumCodigosAppError::ERROR_AEROPOST);
        }

        return false;
    }
}
