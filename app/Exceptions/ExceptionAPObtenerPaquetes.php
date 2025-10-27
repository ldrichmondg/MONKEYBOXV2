<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExceptionAPObtenerPaquetes extends ExceptionAeropost
{
    public function __construct(string $idTracking, $status = null, $body = null, string $message = '', $code = 0, ?Exception $previous = null)
    {
        $message .= "Error al obtener información del paquete con tracking {$idTracking}.";

        if ($status) {
            $message .= " Status HTTP: {$status}.";
        }

        if ($body) {
            $message .= " Respuesta: " . substr($body, 0, 200) . '...'; // evita log gigante
        }
        // internalCode 1007 sera error interno propio para errores de no recibir la prealerta actualizada de Aeropost
        parent::__construct($message, 1007, $code, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse|bool
    {
        if($request->expectsJson()){
            return response()->error('Hubo un error al obtener la informacion del paquete en Aeropost', 'Error al obtener la informacion del paquete en Aeropost', 500, EnumCodigosAppError::ERROR_AEROPOST);
        }

        return false;
    }
}
