<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProveedorController extends Controller
{
    public function ConsultaJson(): JsonResponse{

        try{

            $proveedores = Proveedor::all();
            return response()->json($proveedores);

        }catch(\Exception $e){

            Log::error('ProveedorController->ConsultarJson->error:'.$e);

            return response()->json([
                'status' => 'error',
                'message' => 'Algo ocurrio al consultar los proveedores. Ver el Log',
            ], 500); // 500 = Internal Server Error
        }
    }
}
