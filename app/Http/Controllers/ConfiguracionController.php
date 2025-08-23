<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    public function Consultar(): JsonResponse
    {

        try {

            $config = json_decode(Storage::get('configuracion.json'), true);

            return response()->json($config);

        } catch (\Exception $e) {

            Log::error('ConfiguracionController->Consultar->error:'.$e);

            return response()->json([
                'status' => 'error',
                'message' => 'Algo ocurrio al consultar la configuracion. Ver el Log',
            ], 500); // 500 = Internal Server Error
        }
    }
}
