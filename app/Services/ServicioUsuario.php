<?php

namespace App\Services;

use App\Events\EventoRegistroUsuario;
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

class ServicioUsuario
{
    public static function Filtro($request): ?LengthAwarePaginator
    {
        try {
            $query = $request->input('buscar');
            // Aquí va tu lógica, por ejemplo:
            $usuarios = User::select('CEDULA', 'NOMBRE', 'APELLIDOS', 'TELEFONO', 'email', 'id');

            if (! empty($query)) {

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

    /**
     * @param int $id
     * @return void
     * @throws QueryException
     */
    public static function Eliminar(int $id): void {
        User::destroy($id);
    }

    /**
     * @param int $idUsuario
     * @param string $nombre
     * @param string $apellidos
     * @param string $telefono
     * @param string $email
     * @param string $empresa
     * @return void
     * @throws ModelNotFoundException
     */
    public static function Actualizar(int $idUsuario, string $nombre, string $apellidos, string $telefono, string $email, string|null $empresa): void {

        $usuario = User::findOrFail($idUsuario);
        $usuario->NOMBRE = $nombre;
        $usuario->APELLIDOS = $apellidos;
        $usuario->TELEFONO = $telefono;
        $usuario->email = $email;
        $usuario->EMPRESA = $empresa;
        $usuario->save();
        Log::info('Usuario actualizado exitosamente');
    }

    /**
     * @param string $nombre
     * @param string $apellidos
     * @param string $telefono
     * @param string $email
     * @param string|null $empresa
     * @return void
     */
    public static function Registrar(string $nombre, string $apellidos, string $telefono, string $email, string|null $empresa): void {

        $pass = Str::random(10);

        $usuario = new User();
        $usuario->NOMBRE = $nombre;
        $usuario->APELLIDOS = $apellidos;
        $usuario->TELEFONO = $telefono;
        $usuario->email = $email;
        $usuario->EMPRESA = $empresa;
        $usuario->IDPERFIL = 2;
        $usuario->password = $pass; //se hace hash en el modelo
        $usuario->save();

        EventoRegistroUsuario::dispatch($usuario, $pass);
        event(new Registered($usuario));

        Log::info('Usuario registrado exitosamente');
    }
}
