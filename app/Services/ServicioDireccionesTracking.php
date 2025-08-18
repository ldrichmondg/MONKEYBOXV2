<?php 
namespace App\Services;

use Exception;

use Illuminate\Support\Facades\Auth;
use App\Models\DireccionesTracking;
use Illuminate\Support\Str;


class ServicioDireccionesTracking
{
    public function CrearDireccionTracking($direccion)
    {
        try {
            $direccion = trim($direccion);
            $direccion = Str::upper($direccion);
            
            if(empty($direccion) || !$this->ValidaExisteDireccion($direccion)) {
                return false;
            }
            $direccionTracking = new DireccionesTracking();
            $direccionTracking->DIRECCION = $direccion;
            $direccionTracking->save();
            return $direccionTracking;
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al crear la dirección de tracking: ' . $e->getMessage()], 500);
        }
    }
    public function DireccionTrackingList()
    {
        try {
            $direccionesTracking = DireccionesTracking::select('id', 'DIRECCION')->get();
            return $direccionesTracking;
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al obtener la lista de direcciones de tracking: ' . $e->getMessage()], 500);
        }
    }
    public function ValidaExisteDireccion($direccion)
    {
        try {
            $direccionTracking = DireccionesTracking::where('DIRECCION', $direccion)->count();
            if ($direccionTracking > 0) {
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al validar la dirección de tracking: ' . $e->getMessage()], 500);
        }
    }
}