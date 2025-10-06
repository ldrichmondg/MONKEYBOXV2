<?php

namespace App\Services;

use App\DataTransformers\ModelToIdDescripcionDTO;
use App\Events\EventoRegistroCliente;
use App\Http\Requests\RequestRegistroCliente;
use App\Models\Cliente;
use App\Models\Direccion;
use App\Models\Enum\TipoCardDirecciones;
use App\Models\Enum\TipoDirecciones;
use App\Models\Enum\TipoPerfiles;
use App\Models\Tracking;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServicioCliente
{
    public static function ObtenerClientesSimples(): array
    {
        try {
            $clientes = DB::table('cliente')
                ->join('users', 'users.id', '=', 'cliente.IDUSUARIO')
                ->select('cliente.id', 'users.NOMBRE as NOMBRE', 'users.APELLIDOS as APELLIDOS')
                ->get();

            return ModelToIdDescripcionDTO::map($clientes);
        } catch (Exception $e) {
            Log::error('[ServicioCliente -> ObtenerClientesSimples] Error: ' . $e->getMessage());

            return [];
        }
    }


    public static function Crear(RequestRegistroCliente $request): Cliente
    {

        // contaseña
        $contraseña = bin2hex(random_bytes(8));
        $usuario = User::create([
            'CEDULA' => $request->cedula,
            'NOMBRE' => $request->nombre,
            'email' => $request->correo,
            'password' => $contraseña,
            'IDPERFIL' => TipoPerfiles::Clientes, // id del cliente
            'TELEFONO' => $request->telefono,
            'APELLIDOS' => $request->apellidos,
        ]);

        $cliente = Cliente::create([
            'CASILLERO' => $request->casillero,
            'FECHANACIMIENTO' => $request->fechaNacimiento,
            'IDUSUARIO' => $usuario->id,
        ]);

        $direcciones = $request->input('direcciones');

        foreach ($direcciones as $direccion) {
            Direccion::create([
                'DIRECCION' => $direccion['direccion'],
                'TIPO' => $direccion['tipo'],
                'CODIGOPOSTAL' => $direccion['codigoPostal'],
                'IDCLIENTE' => $cliente->id,
                'PAISESTADO' => $direccion['paisEstado'],
                'LINKWAZE' => $direccion['linkWaze'],
            ]);
        }

        EventoRegistroCliente::dispatch($cliente, $contraseña);
        event(new Registered($usuario));

        return $cliente;

    }

    /**
     * @param $request
     * @return void
     * @throws ModelNotFoundException
     */
    public static function Actualizar($request): void // cambiar esto a datos limpios
    {
        DB::transaction(function () use ($request) {
            Log::info($request->idUsuario);
            // 1. Actualización del usuario
            $item = User::find($request->idUsuario);

            $item->CEDULA = $request->cedula;
            $item->NOMBRE = $request->nombre;
            $item->email = $request->correo;
            $item->APELLIDOS = $request->apellidos;
            $item->TELEFONO = $request->telefono;
            $item->save();

            // actualizar cliente
            $cliente = Cliente::where('IDUSUARIO', $request->idUsuario)->first();
            $cliente->CASILLERO = $request->casillero;
            $cliente->FECHANACIMIENTO = $request->fechaNacimiento;
            $cliente->save();

            // Actualizar direcciones
            $direcciones = $request->input('direcciones');

            //ver cuales direcciones tienen ids positivos, porque esos no son nuevos
            foreach ($direcciones as $dir) {
                $id = $dir['id'];

                if ($id > 0) {
                    // Direccion ya existe en la base
                    $direccion = Direccion::where('id', $id)
                        ->where('IDCLIENTE', $cliente->id)
                        ->first();

                    if ($direccion) {
                        $direccion->update([
                            'DIRECCION' => $dir['direccion'],
                            'TIPO' => $dir['tipo'],
                            'CODIGOPOSTAL' => $dir['codigoPostal'],
                            'IDCLIENTE' => $cliente->id,
                            'PAISESTADO' => $dir['paisEstado'],
                            'LINKWAZE' => $dir['linkWaze'],
                        ]);
                        $idsExistentes[] = $direccion->id;
                    }
                } else {
                    // son direcciones nuevas
                    $nuevaDireccion = $cliente->direcciones()->create([
                        'DIRECCION' => $dir['direccion'],
                        'TIPO' => $dir['tipo'],
                        'CODIGOPOSTAL' => $dir['codigoPostal'],
                        'IDCLIENTE' => $cliente->id,
                        'PAISESTADO' => $dir['paisEstado'],
                        'LINKWAZE' => $dir['linkWaze'],
                    ]);
                    $idsExistentes[] = $nuevaDireccion->id;
                }
            }

            // Elimina direcciones que no llegaron desde el frontend
            $cliente->direcciones()
                ->whereNotIn('id', $idsExistentes)
                ->delete();
        });
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
            exit();
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

        try {

            $idDireccion = $tracking->IDDIRECCION;
            $direccion = Direccion::where('ID', $idDireccion)->first();
            // $direccionCliente = $tracking->direccion()->first();
            $cliente = $direccion->cliente()->first();

            return $cliente->usuario()->first();

        } catch (Exception $e) {

            Log::error('[ServicioCliente->ObtenerCliente] error:' . $e);

            return null;
        }
    }

    public static function Eliminar(int $idCliente): array{
        // 1. Validar que no hayan paquetes != a Pagado
        // 1.1 Si hay paquetes no se puede eliminar, tirar error pero enviamos los trackings enlazados
        // 2. Si t#do OK, eliminarlo
        $cliente = Cliente::findOrFail($idCliente);
        $direcciones = $cliente->direcciones;
        $usuario = $cliente->usuario;
        $hayTrackingsEnProceso = false;
        $trackingsEnProceso = [];

        Log::info('PASA2');
        // 1. Validar que no hayan paquetes != a Pagado
        foreach($direcciones as $direccion){
            $trackings = $direccion->trackings;

            foreach($trackings as $tracking){
                if($tracking->ESTADOMBOX != 'Facturado'){
                    Log::info('ENTRO');
                    $hayTrackingsEnProceso = true;
                    $trackingsEnProceso[] = $tracking;
                    break;
                }
            }

            if($hayTrackingsEnProceso) break;

        }

        Log::info('PASA3');
        if($hayTrackingsEnProceso){
            Log::info(json_encode($trackingsEnProceso));
            return $trackingsEnProceso;
        }

        //$usuario->delete();
        //$cliente->delete();
        return [];
    }
}
