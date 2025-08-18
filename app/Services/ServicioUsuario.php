<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ServicioUsuario{

    public static function Filtro($request): ?LengthAwarePaginator
    {
        try {
            $query = $request->input('buscar');
            // Aquí va tu lógica, por ejemplo:
            $usuarios = User::select('CEDULA', 'NOMBRE', 'APELLIDOS', 'TELEFONO', 'email', 'id');

            if (!empty($query)) {

                $usuarios->where(function ($q) use ($query) {
                    $q->where('CEDULA', 'like', "%{$query}%")
                        ->orWhere('NOMBRE', 'like', "%{$query}%")
                        ->orWhere('APELLIDOS', 'like', "%{$query}%")
                        ->orWhere('TELEFONO', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");
                });
            }

            return $usuarios->paginate(8);
        } catch (\Throwable $e) {
            // Opcional: loguear el error
            Log::error('Error en ServicioUsuario::Filtro', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public static function Editar($request): array{

        try{
            $item = User::find($request->idUsuario);

            $currentUser = Auth::user();
            if($item->id!=$currentUser->id && $currentUser->NAME!="Administrador"){
                redirect()->route('noAccess');
            }
            $item->CEDULA = $request->cedula;
            $item->NOMBRE = $request->nombre;
            $item->email = $request->email;
            $item->APELLIDOS = $request->apellidos;
            $item->TELEFONO = $request->telefono;

            $item->save();

            return [
                'state' => 'Exito',
                'mensaje' => 'Se actualizo el usuario de forma exitosa'
            ];

        } catch (Exception $e) {

            return [
                'state' => 'Error',
                'mensaje' => 'Hubo un error al actualizar el usuario'
            ];
        }
    }

}
