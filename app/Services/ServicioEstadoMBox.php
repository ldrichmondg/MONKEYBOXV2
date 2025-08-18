<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServicioEstadoMBox
{

    /**
     * @param string[] $estados
     * @return EstadoMBox[]|null
     */
    public static function ObtenerEstadosMBox(array $estados): ?array {

        try{

            $estados = DB::table('estadombox')
                ->select('DESCRIPCION', 'COLORCLASS')
                ->whereIn('DESCRIPCION', $estados)
                ->get();

            return $estados->toArray();

        }catch (\Throwable $th){

            Log::error('[ServicioEstadoMBox->ObtenerEstadosMBox] error:'.$th);
            return null;
        }
    }
}
