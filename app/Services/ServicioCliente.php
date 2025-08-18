<?php

namespace App\Services;

use App\DataTransformers\ModelToIdDescripcionDTO;
use App\Events\EventoRegistroCliente;
use App\Models\Cliente;
use App\Models\Direccion;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Enum\TipoCardDirecciones;
use App\Models\Enum\TipoDirecciones;
use App\Models\Tracking;
use App\Models\TrackingHistorial;
use Diccionarios;

class ServicioCliente
{

    public static function ObtenerClientesSimples(): array{
        try{
            $clientes = DB::table('cliente')
                ->join('users', 'users.id', '=', 'cliente.IDUSUARIO')
                ->select('cliente.id', 'users.NOMBRE as NOMBRE', 'users.APELLIDOS as APELLIDOS' )
                ->get();

            return ModelToIdDescripcionDTO::map($clientes);
        }catch(Exception $e){
            Log::error("[ServicioCliente -> ObtenerClientesSimples] Error: ".$e->getMessage());
            return [];
        }
    }

    public static function Filtro($request): ?LengthAwarePaginator
    {
        try {
            $query = $request->input('buscar');
            // Aquí va tu lógica, por ejemplo:
            $clientes = Cliente::select('cliente.id', 'CASILLERO', 'IDUSUARIO')
                ->join('users as u', 'cliente.IDUSUARIO', '=', 'u.id')
                ->orderBy('u.NOMBRE')
                ->with('usuario');

            if (!empty($query)) {

                $clientes->where(function ($q) use ($query) {
                    $q->orWhereHas('usuario', function ($subq) use ($query) {

                        $subq->where('CEDULA', 'like', "%{$query}%")
                            ->orWhere('NOMBRE', 'like', "%{$query}%")
                            ->orWhere('APELLIDOS', 'like', "%{$query}%")
                            ->orWhere('TELEFONO', 'like', "%{$query}%")
                            ->orWhere('email', 'like', "%{$query}%");
                    });
                });
            }

            return $clientes->paginate(8);
        } catch (\Throwable $e) {
            // Opcional: loguear el error
            Log::error('Error en ServicioCliente::Filtro', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public static function Crear($request): ?Cliente
    {
        try {

            //contaseña
            $contraseña = bin2hex(random_bytes(8));
            $usuario = User::create([
                'CEDULA' => $request->cedula,
                'NOMBRE' => $request->nombre,
                'email' => $request->email,
                'password' => $contraseña,
                'IDPERFIL' => 3, //id del cliente
                'TELEFONO' => $request->telefono,
                'APELLIDOS' => $request->apellidos,
            ]);

            $cliente = Cliente::create([
                'CASILLERO' => $request->casillero,
                'FECHANACIMIENTO' => $request->fechaNacimiento,
                'IDUSUARIO' => $usuario->id,
            ]);

            $tienePrincipal = false;
            $direcciones = $request->input('direcciones');
            // Verificar si ya hay alguna dirección con TIPO 1
            foreach (array_reverse($request->input('direcciones')) as $direccion) {
                if ($direccion['tipo'] == 1) {
                    $tienePrincipal = true;
                    break;  // No es necesario seguir buscando, ya que encontramos una con tipo 1
                }
            }
            // Si no se encontró una dirección principal, asignar la primera dirección como principal
            if (!$tienePrincipal && count($request->input('direcciones')) > 0) {
                $direcciones[0]['tipo'] = 1;  // Asignar TIPO = 1 a la primera dirección
            }

            foreach ($direcciones as $direccion) {
                Direccion::create([
                    'DIRECCION' => $direccion['direccion'],
                    'TIPO' => $direccion['tipo'],
                    'CODIGOPOSTAL' => $direccion['codigoPostal'],
                    'IDCLIENTE' => $cliente->id,
                    'PAISESTADO' => $direccion['paisEstado'],
                    'LINKWAZE' => $direccion['linkWaze']
                ]);
            }

            EventoRegistroCliente::dispatch($cliente, $contraseña);
            event(new Registered($usuario));

            return $cliente;
        } catch (Exception $e) {
            // Opcional: loguear el error
            //var_dump($e, $e->getMessage());
            Log::error('Error en ServicioCliente::Crear', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public static function Editar($request)
    {

        try {
            $item = User::find($request->idUsuario);

            $item->CEDULA = $request->cedula;
            $item->NOMBRE = $request->nombre;
            $item->email = $request->email;
            $item->APELLIDOS = $request->apellidos;
            $item->TELEFONO = $request->telefono;
            $item->save();

            $cliente = Cliente::where("IDUSUARIO", $request->idUsuario)->first();
            $cliente->CASILLERO = $request->casillero;
            $cliente->save();

            $tienePrincipal = false;

            // Verificar si ya hay alguna dirección con TIPO 1
            foreach (array_reverse($request->input('direcciones')) as $direccion) {
                if ($direccion['TIPO'] == 1) {
                    $tienePrincipal = true;
                    break;  // No es necesario seguir buscando, ya que encontramos una con tipo 1
                }
            }

            //Direccion::where('IDCLIENTE', $cliente->id)->delete();

            $direcciones = $request->input('direcciones');
            // Si no se encontró una dirección principal, asignar la primera dirección como principal
            if (!$tienePrincipal && count($direcciones) > 0) {
                $direcciones[0]['TIPO'] = 1;  // Asignar TIPO = 1 a la primera dirección
            }

            foreach ($direcciones as $dir) {
                $id = $dir['id'];

                if ($id > 0) {
                    // Direccion ya existe en la base
                    $direccion = Direccion::where('id', $id)
                        ->where('IDCLIENTE', $cliente->id)
                        ->first();

                    if ($direccion) {
                        $direccion->update([
                            'DIRECCION' => $dir['DIRECCION'],
                            'TIPO' => $dir['TIPO'],
                            'CODIGOPOSTAL' => $dir['CODIGOPOSTAL'],
                            'IDCLIENTE' => $cliente->id,
                            'PAISESTADO' => $dir['PAISESTADO'],
                            'LINKWAZE' => $dir['LINKWAZE']
                        ]);
                        $idsExistentes[] = $direccion->id;
                    }
                } else {
                    $nueva = $cliente->direcciones()->create([
                        'DIRECCION' => $dir['DIRECCION'],
                        'TIPO' => $dir['TIPO'],
                        'CODIGOPOSTAL' => $dir['CODIGOPOSTAL'],
                        'IDCLIENTE' => $cliente->id,
                        'PAISESTADO' => $dir['PAISESTADO'],
                        'LINKWAZE' => $dir['LINKWAZE']
                    ]);
                    $idsExistentes[] = $nueva->id;
                }
            }

            // Elimina direcciones que no llegaron desde el frontend
            $cliente->direcciones()
                ->whereNotIn('id', $idsExistentes)
                ->delete();

            return $cliente;
        } catch (Exception $e) {
            var_dump($e->getMessage());
            die();

            return [
                'state' => 'Error',
                'mensaje' => 'Hubo un error al actualizar el usuario'
            ];
        }
    }
    public function CalcularDashboardCliente()
    {
        try {
            $cantidadClientesActuales = Cliente::whereNull('deleted_at')->count();

            $finMesPasado = now()->subMonth()->endOfMonth();
            $cantidadClientesMesPasado = Cliente::where('created_at', '<=', $finMesPasado)
                ->where(function ($query) use ($finMesPasado) {
                    $query->whereNull('deleted_at')
                        ->orWhere('deleted_at', '>', $finMesPasado);
                })
                ->count();
            $promedio = $cantidadClientesMesPasado / $cantidadClientesActuales;
            $promedio = $promedio != 0 ? $promedio : 1;
            $porcentajeClientesMesPasado = $cantidadClientesActuales > 0
                ? $promedio * 100
                : 0;
            return [
                'clientes_actuales' => $cantidadClientesActuales,
                'clientes_mes_pasado' => round($porcentajeClientesMesPasado, 2),
            ];
        } catch (Exception $e) {
            throw new \Exception('Error al calcular los clientes: ' . $e->getMessage());
        }
    }
    public function CantidadClientesMeses()
    {
        try {
            $clientesPorMes = [];

            for ($mes = 1; $mes <= 12; $mes++) {
                $cantidad = Cliente::whereMonth('created_at', $mes)
                    ->whereYear('created_at', now()->year) // Opcional: solo este año
                    ->whereNull('deleted_at') // Solo clientes no eliminados
                    ->count();

                $clientesPorMes[] = $cantidad;
            }

            return $clientesPorMes;
        } catch (\Exception $e) {
            throw new \Exception('Error al calcular los clientes por meses: ' . $e->getMessage());
        }
    }

    public function ListaDireccionesMapa(int $tipoCardDirecciones)
    {
        try {
            $paisesEstado = null;
            switch ($tipoCardDirecciones) {
                case TipoCardDirecciones::CLIENTES->value:
                    $paisesEstado = $this->GetDireccionesPrincipalesClientes();
                    break;
                case TipoCardDirecciones::TRANSITO->value:
                    $paisesEstado = $this->GetDireccionesPaquetes(false);
                    break;
                case TipoCardDirecciones::ENTREGADOS->value:
                    $paisesEstado = $this->GetDireccionesPaquetes(true);
                    break;
                default:
                    throw new \Exception('Tipo de dirección no válida');
            }


            return $paisesEstado;
        } catch (Exception $e) {
            var_dump($e->getMessage());
            die();
            Log::error('Error al obtener la lista de direcciones del mapa: ' . $e->getMessage());
            return null;
        }
    }
    private function GetDireccionesPrincipalesClientes()
    {
        try {
            $paisesEstado = Direccion::whereNull('deleted_at')
                ->where('TIPO', TipoDirecciones::PRINCIPAL->value)
                ->select('PAISESTADO', 'CODIGOPOSTAL', 'DIRECCION');

            return $paisesEstado->distinct()->get();
        } catch (Exception $e) {
            Log::error('Error al obtener la lista de direcciones del mapa: ' . $e->getMessage());
            return null;
        }
    }

    private function GetDireccionesPaquetes($entregado)
    {
        try {
            $trackings = Tracking::where('ENTREGADOCOSTARICA', $entregado)
                ->where('ENTREGADOCLIENTE', $entregado)
                ->get();

            $direcciones = collect();

            foreach ($trackings as $tracking) {
                if ($entregado) {
                    $direcciones->push([
                        'PAISESTADO' => $tracking->direccion->PAISESTADO,
                        'CODIGOPOSTAL' => $tracking->direccion->CODIGOPOSTAL,
                        'DIRECCION' => $tracking->direccion->DIRECCION,
                    ]);
                } else {
                    $ultimo = $tracking->UltimoPaisEstado();

                    if ($ultimo) {
                        $direcciones->push([
                            'PAISESTADO' => $ultimo,
                            'CODIGOPOSTAL' => $ultimo,
                            'DIRECCION' => $ultimo,
                        ]);
                    }
                }
            }
            // Eliminar duplicados basados en PAISESTADO, CODIGOPOSTAL y DIRECCION
            return $direcciones->unique(function ($item) {
                return $item['PAISESTADO'] . '|' . $item['CODIGOPOSTAL'] . '|' . $item['DIRECCION'];
            })->values();
        } catch (Exception $e) {
            var_dump($e->getMessage());

            Log::error('Error al obtener la lista de direcciones del mapa: ' . $e->getMessage());
            return null;
        }
    }

    public static function ObtenerCliente($tracking): ?User
    {

        try{

            $idDireccion = $tracking->IDDIRECCION;
            $direccion = Direccion::where('ID', $idDireccion)->first();
            //$direccionCliente = $tracking->direccion()->first();
            $cliente = $direccion->cliente()->first();
            return $cliente->usuario()->first();

        } catch(Exception $e){

            Log::error('[ServicioCliente->ObtenerCliente] error:'.$e);
            return null;
        }
    }
}
