<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExceptionAPRequestActualizarPrealerta extends ExceptionAeropost
{
    public function __construct($message = 'La respuesta no actualizó la prealerta', $code = 0, ?Exception $previous = null)
    {
        // internalCode 1004 sera error interno propio para errores de no recibir la prealerta actualizada de Aeropost
        parent::__construct($message, 1005, $code, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse|bool
    {
        if($request->expectsJson()){
            return response()->error('Hubo un error al actualizar la prealerta en Aeropost', 'Error al actualizar la prealerta en Aeropost', 500, EnumCodigosAppError::ERROR_AEROPOST);
        }

        return false;
    }
}
