<?php

namespace App\Services;

use App\Events\EventoRegistroUsuario;
use App\Models\Imagen;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ServicioImagenes
{
    public static function EliminarImagenesProveedor(int $idTracking, int $tipoImagen){
        // 1. Eliminar las imagenes de un tracking y proveedor especifico

        Imagen::where('IDTRACKING', $idTracking)
            ->where('TIPOIMAGEN', $tipoImagen)
            ->delete();
    }
}
