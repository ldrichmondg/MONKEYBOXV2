<?php

namespace App\Services;

use App\Models\Direccion;
use \Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServicioDireccion
{
    /**
     * @param string[] $estados
     * @return Direccion[]|null
     */
    public static function ObtenerDirecciones(int $idCliente, bool $conRelacionCliente = false): Collection
    {

        if ($conRelacionCliente) {
            $direcciones = Direccion::with('cliente')
                ->where('IDCLIENTE', $idCliente)
                ->select('id', 'DIRECCION', 'TIPO', 'IDCLIENTE', 'CODIGOPOSTAL', 'PAISESTADO', 'LINKWAZE')
                ->get();
        } else {
            $direcciones = Direccion::where('IDCLIENTE', $idCliente)
                ->select('id', 'DIRECCION', 'TIPO', 'IDCLIENTE', 'CODIGOPOSTAL', 'PAISESTADO', 'LINKWAZE')
                ->get();
        }

        return $direcciones;

    }
}
