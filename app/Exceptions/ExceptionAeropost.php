<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExceptionAeropost extends Exception
{
    protected $internalCode;

    public function __construct($message = '', $internalCode = 0, $code = 0, ?Exception $previous = null)
    {
        $this->internalCode = $internalCode;
        parent::__construct($message, $code, $previous);
    }

    public function getInternalCode()
    {
        return $this->internalCode;
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse|bool
    {
        if($request->expectsJson()){
            return response()->error('Hubo un error al comunicarse con la app de Aeropost', 'Error al comunicarse con Aeropost', 500, EnumCodigosAppError::ERROR_AEROPOST);
        }

        return false;
    }
}
