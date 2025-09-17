<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExceptionTrackingProveedorNotFound extends Exception
{
    public function __construct($message = 'No se encontró el trackingProveedor', $code = 0, ?Exception $previous = null)
    {
        // internalCode 1011 sera error interno propio para errores de no encontrar couriers
        parent::__construct($message, 1011, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse|bool
    {
        if($request->expectsJson()){

            return response()->error('No se encontró la prealerta', 'Error al encontrar la prealerta', 404, EnumCodigosAppError::PREALERTA_NOT_FOUND);
        }

        return false; //false es para que siga el flujo normal de laravel que maneja las excepciones
    }
}
