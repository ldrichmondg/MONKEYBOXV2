<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExceptionPrealertaNotFound extends Exception
{
    public function __construct($message = 'No se encontraron la prealerta relacionada al tracking', $code = 0, ?Exception $previous = null)
    {
        // internalCode 1010 sera error interno propio para prealertas no encontradas
        parent::__construct($message, 1010, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse|bool
    {
        if($request->expectsJson()){

            return response()->error('No se encontró la prealerta', 'Error al encontrar la prealerta', 404, EnumCodigosAppError::PREALERTA_NOT_FOUND);
        }

        return false;
    }
}
