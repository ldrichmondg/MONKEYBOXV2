<?php

namespace App\Services;

use App\Events\EventoClienteEnlazadoPaquete;
use App\Events\EventoCorreoEliminarTracking;
use App\Events\EventoFacturaGenerada;
use App\Events\FacturaGenerada;
use Exception;
use App\Models\Tracking;
use App\Models\Direccion;
use App\Models\Cliente;
use App\Models\TrackingHistorial;
use App\Models\Enum\TipoHistorialTracking;
use Illuminate\Support\Facades\Auth;
use DateTime;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailAvisoCostaRica;
use App\Mail\EmailAvisoCliente;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ServicioTrackingViejo
{
    protected $servicioHttp;
    protected $trackingModel;
    protected $direccionModel;
    protected $clienteModel;
    protected $servicioDireccionesTracking;

    public function __construct(HttpService $servicioHttp, ServicioDireccionesTracking $servicioDireccionesTracking, Tracking $trackingModel, Direccion $direccionModel, Cliente $clienteModel)
    {
        $this->servicioHttp = $servicioHttp;
        $this->trackingModel = $trackingModel;
        $this->direccionModel = $direccionModel;
        $this->clienteModel = $clienteModel;
        $this->servicioDireccionesTracking = $servicioDireccionesTracking;
    }

    private function RetornaValorAtributo($atributos, $nombre)
    {
        foreach ($atributos as $atributo) {
            if ($atributo['l'] === $nombre) {
                return $atributo['val'];
            }
        }
        return NULL;
    }

    private function SeparaLugar($lugar)
    {
        $postalCode = 0;
        $lugarSinCodigoPostal = $lugar;
        if (!empty($lugar)) {
            $ultimaComma = strrpos($lugar, ',');
            if ($ultimaComma !== false) {
                $posiblePostalCode = trim(substr($lugar, $ultimaComma + 1));
                if (is_numeric($posiblePostalCode)) {
                    $postalCode = $posiblePostalCode;
                    $lugarSinCodigoPostal = trim(substr($lugar, 0, $ultimaComma));
                }
            }
        }
        return [$lugarSinCodigoPostal, $postalCode];
    }

    public function ConstruirTracking($idTracking, $guardar = false, $respuesta = null)
    {
        try {

            if ($respuesta == null) {
                $respuestaObjeto = $this->RastrearEnvio($idTracking);
            } else {
                $respuestaObjeto = $respuesta;
            }

            if (isset($respuestaObjeto['estado']) && $respuestaObjeto['estado'] == "proceso") {
                return $respuestaObjeto;
            }

            if (!isset($respuestaObjeto)) {
                throw new Exception("Respuesta inválida o sin datos");
            }

            $datos = $respuestaObjeto['datos'] ?? $respuestaObjeto;
            if (isset($datos['error'])) {
                return ['error' => $datos['error']];
            }

            $shipment = $datos['shipments'][0];
            $attributes = $shipment['attributes'];
            $arrayCarries = $shipment['carriers'];

            $tracking = new Tracking();
            $tracking->IDAPI = 0;
            $tracking->IDTRACKING = $shipment['trackingId'];
            $tracking->DESCRIPCION = "";
            $tracking->DESDE = $this->RetornaValorAtributo($attributes, "from") ?? "";
            $tracking->HASTA = $this->RetornaValorAtributo($attributes, "to") ?? "";
            $tracking->DESTINO = $this->RetornaValorAtributo($attributes, "destination") ?? "";
            $tracking->COURIER = implode(", ", $arrayCarries);
            $tracking->DIASTRANSITO = $this->RetornaValorAtributo($attributes, "days_transit") ?? 0;
            $tracking->PESO = 0.000;
            $tracking->IDDIRECCION = 1;
            $tracking->IDUSUARIO = Auth::user()->id ?? 1;

            $historiales = [];
            $cont = -1;
            if (!empty($shipment['states'])) {
                foreach ($shipment['states'] as $state) {
                    $courierCodigoJson = $state['carrier'];

                    $lugar = $this->SeparaLugar(!empty($state['location']) ? $state['location'] : "");

                    $evento = new TrackingHistorial();
                    $evento->id = $cont;
                    $evento->DESCRIPCION = $state['status'];
                    $evento->DESCRIPCIONMODIFICADA = '';
                    $evento->PAISESTADO = $lugar[0];
                    $evento->CODIGOPOSTAL = $lugar[1];
                    $evento->OCULTADO = !empty($state['require_fields']);
                    $evento->TIPO = TipoHistorialTracking::API->value;
                    $evento->IDTRACKING = $shipment['trackingId'];
                    $evento->FECHA = (new \DateTime($state['date']))->format('Y-m-d H:i:s');
                    $evento->IDCOURIER = $tracking->courrierNombreAId($arrayCarries[$courierCodigoJson]);


                    $historiales[] = $evento;
                    $cont = $cont - 1;
                }
            }

            if ($guardar) {
                // Guardar tracking real en BD
                $tracking->save();

                $this->servicioDireccionesTracking->CrearDireccionTracking($tracking->DESDE);
                $this->servicioDireccionesTracking->CrearDireccionTracking($tracking->HASTA);
                $this->servicioDireccionesTracking->CrearDireccionTracking($tracking->DESTINO);

                foreach ($historiales as $evento) {
                    $historial = new TrackingHistorial();
                    $historial->DESCRIPCION = $evento->DESCRIPCION;
                    $historial->DESCRIPCIONMODIFICADA = $evento->DESCRIPCIONMODIFICADA;
                    $historial->PAISESTADO = $evento->PAISESTADO;
                    $historial->CODIGOPOSTAL = $evento->CODIGOPOSTAL;
                    $historial->OCULTADO = $evento->OCULTADO;
                    $historial->TIPO = $evento->TIPO;
                    $historial->IDTRACKING = $tracking->id;
                    $historial->FECHA = $evento->FECHA;
                    $historial->IDCOURIER = $evento->IDCOURIER;
                    $historial->save();
                }

                $usuario = $this->ObtenerCliente($tracking);
                if (!empty($usuario)) {
                    EventoClienteEnlazadoPaquete::dispatch($tracking, $usuario);
                }

                return ['tracking' => $tracking];
            }

            $tracking->historialesT = $historiales;
            $tracking->COURIER = implode(", ", $arrayCarries);

            return [
                'tracking' => $tracking,
                'historial' => $historiales
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }


    private function ObtenerCliente($tracking)
    {

        $idDireccion = $tracking->IDDIRECCION;
        $direccion = Direccion::where('ID', $idDireccion)->first();
        //$direccionCliente = $tracking->direccion()->first();
        $cliente = $direccion->cliente()->first();
        return $cliente->usuario()->first();
    }
    private function RastrearEnvio($numeroSeguimiento)
    {
        try {
            $apiKey = env('PARCELSAPP_API_KEY');
            $urlSeguimiento = env('PARCELSAPP_URL_SEGUIMIENTO');
            $datosRespuesta = [];
            // Datos del envío
            $envios = [
                [
                    'trackingId' => $numeroSeguimiento,
                    'language'   => 'es',
                    'country'    => 'Costa Rica'
                ],
            ];

            // Iniciar la solicitud de seguimiento
            $respuesta = $this->servicioHttp->postRequest($urlSeguimiento, [
                'apiKey'   => $apiKey,
                'shipments' => $envios,
            ]);

            if ($respuesta->successful()) {
                $datosRespuesta = $respuesta->json();

                $uuid = $datosRespuesta['uuid'] ?? "";

                if (empty($uuid) && !empty($datosRespuesta['shipments'])) {
                    return $datosRespuesta;
                }

                // Verificar el estado del seguimiento con UUID
                //$estadoSeguimiento = $this->VerificarEstadoSeguimiento($uuid);
                $estadoSeguimiento = ['estado' => 'proceso', 'datos' => $uuid];
                return $estadoSeguimiento;
            } else {

                throw new Exception("Error en la solicitud POST: " . $respuesta->status());
            }
        } catch (Exception $e) {

            throw new Exception("Numero de tracking no encontrado" . $e->getMessage());
        }
    }

    public function VerificarEstadoSeguimiento($uuid)
    {
        try {
            $apiKey = env('PARCELSAPP_API_KEY');
            $urlSeguimiento = env('PARCELSAPP_URL_SEGUIMIENTO');

            $respuesta = $this->servicioHttp->getRequest($urlSeguimiento, [
                'apiKey' => $apiKey,
                'uuid'   => $uuid,
            ]);

            if ($respuesta->successful()) {
                // Convertir la respuesta a array
                $datosRespuesta = $respuesta->json();

                if ($datosRespuesta['done']) {
                    return ['estado' => 'Seguimiento completo', 'datos' => $datosRespuesta];
                } else {
                    return ['estado' => 'proceso', 'datos' => $uuid];
                }
            } else {
                throw new Exception("Error en la consulta GET: " . $respuesta->body());
            }
        } catch (Exception $e) {
            throw new Exception("Error en VerificarEstadoSeguimiento: " . $e->getMessage());
        }
    }
    public function detalleTracking($idTracking)
    {
        //1.idTracking que ponga en el usuario ya exista en la bd
        //Si no existe en la BD llamar a la api
        //Si la api no retorna no exste en ningun lado mandar msj

        try {

            //Verificar si esta en la BD

            $tracking = Tracking::where('IDTRACKING', $idTracking)->first();

            if ($tracking != null) {
                return $tracking;
            }
            return null;
        } catch (Exception $e) {
            throw new Exception("Error en Detalles: " . $e->getMessage());
            return null;
        }
    }

    public function historialesTracking($idTracking)
    {
        try {

            $tracking = Tracking::where('IDTRACKING', $idTracking)->first();
            if ($tracking != null) {
                $historial = TrackingHistorial::where('IDTRACKING', $tracking->id)->get();
                return $historial;
            }
            return null;
        } catch (Exception $e) {
            throw new Exception("Error en Historiales: " . $e->getMessage());
            return null;
        }
    }

    public function Actualizar($request): bool
    {
        try {


            $tracking = Tracking::where('IDTRACKING', $request->idTracking)->first();
            if (!$tracking) {
                return false;
            }

            if ($request->RUTAFACTURA != "-1") {
                $tracking->RUTAFACTURA = $request->RUTAFACTURA;
            }

            $tracking->DESCRIPCION = $request->descripcion;
            $tracking->PESO = $request->peso;

            $tracking->IDUSUARIO = Auth::user()->id ?? 1;
            $tracking->IDDIRECCION = $request->idDireccion;

            $tracking->save();

            return true;
        } catch (Exception $e) {

            throw new Exception('Error al actualizar el tracking: ' . $e->getMessage());
            return false;
        }
    }
    public function EnviaCorreos($tracking, $trackingViejo)
    {
        try {

            if ($tracking == null) {
                return;
            }

            $usuario = $this->ObtenerCliente($tracking); //#
            $usuarioViejo = $this->ObtenerCliente($trackingViejo);

            if ($usuario == null || empty($usuario->email)) {
                return;
            }

            if ($tracking->ENTREGADOCOSTARICA && !$trackingViejo->ENTREGADOCOSTARICA) {
                Mail::to($usuario->email)->send(new EmailAvisoCostaRica($usuario, $tracking));
            } elseif (!$tracking->ENTREGADOCOSTARICA && $trackingViejo->ENTREGADOCOSTARICA) {
                Mail::to($usuario->email)->send(new EmailAvisoCostaRica($usuario, $tracking, true));
            }

            if ($tracking->ENTREGADOCLIENTE && !$trackingViejo->ENTREGADOCLIENTE) {
                Mail::to($usuario->email)->send(new EmailAvisoCliente($usuario, $tracking));
            } elseif (!$tracking->ENTREGADOCLIENTE && $trackingViejo->ENTREGADOCLIENTE) {
                Mail::to($usuario->email)->send(new EmailAvisoCliente($usuario, $tracking, true));
            }

            if ($usuario->id != $usuarioViejo->id) {
                EventoClienteEnlazadoPaquete::dispatch($tracking, $usuario);
                EventoClienteEnlazadoPaquete::dispatch($tracking, $usuarioViejo, true);
            }
        } catch (Exception $e) {
            // Registrar el error (opcional)
            die($e->getMessage());
        }
    }
    public function EnviaCorreosEnlazado($tracking)
    {
        try {

            if ($tracking == null) {
                return;
            }

            $usuario = $this->ObtenerCliente($tracking);

            if ($usuario == null || empty($usuario->email)) {
                return;
            }

            if ($tracking->ENTREGADOCOSTARICA) {
                Mail::to($usuario->email)->send(new EmailAvisoCostaRica($usuario, $tracking));
            }

            if ($tracking->ENTREGADOCLIENTE) {
                Mail::to($usuario->email)->send(new EmailAvisoCliente($usuario, $tracking));
            }
        } catch (Exception $e) {
            // Registrar el error (opcional)
            die($e->getMessage());
        }
    }
    public function ActualizarHistorial($request): bool
    {
        try {
            $historial = TrackingHistorial::where('ID', $request->idHistorial)->first();

            if (!$historial) {
                return false;
            }

            // Actualizar los campos del historial
            $historial->DESCRIPCIONMODIFICADA = $request->descripcion;

            if (isset($request->ocultado)) {
                $historial->OCULTADO = $request->ocultado ? true : false;
            }

            $historial->save();

            return true;
        } catch (Exception $e) {

            throw new Exception('Error al actualizar el historial: ' . $e->getMessage());
            return false; // Retornar false si ocurre un error
        }
    }

    public function ActualizarOculto($request): bool
    {
        try {
            $historial = TrackingHistorial::where('ID', $request->idHistorial)->first();

            if (!$historial) {
                return false;
            }
            if (isset($request->ocultado)) {
                $historial->OCULTADO = $request->ocultado ? true : false;
            }

            $historial->save();

            return true;
        } catch (Exception $e) {
            // Registrar el error
            throw new Exception('Error al actualizar el historial: ' . $e->getMessage());
            return false; // Retornar false si ocurre un error
        }
    }

    public function CrearHistorial($request)
    {
        try {
            // Encontramos el tracking existente usando el ID de Tracking recibido
            $tracking = Tracking::where('IDTRACKING', $request->idTracking)->first();

            // Si no existe el tracking, retornamos un error
            if (!$tracking) {
                return response()->json([
                    'error' => 'Tracking no encontrado.',
                ], 404);
            }

            // Crear un nuevo historial de Tracking
            $historialTracking = new TrackingHistorial();
            $historialTracking->DESCRIPCION = $request->descripcion;
            $historialTracking->CODIGOPOSTAL = 0;
            $historialTracking->PAISESTADO =  $request->descripcion == 'Ya llegó tu paquete ¡Estamos para servirte!' ? $tracking->direccion->PAISESTADO  : 'Costa Rica';
            $historialTracking->DESCRIPCIONMODIFICADA = $request->descripcionModificada ?? "";
            $historialTracking->OCULTADO = 0;
            $historialTracking->TIPO = 2;
            $fecha = isset($request->date)
                ? new DateTime($request->date)
                : new DateTime(); // ahora mismo

            $historialTracking->FECHA = $fecha->format('Y-m-d H:i:s');

            $historialTracking->IDTRACKING = $tracking->id;  // Relacionamos con el tracking existente
            $historialTracking->IDCOURIER = 20;  // Usamos el ID de Courier recibido
            if ($request->tipo == 1) {
                $historialTracking->COSTARICA = true;
            } elseif ($request->tipo == 2) {
                $historialTracking->COSTARICA = true;
                $historialTracking->CLIENTE = true;
            } else {
                $historialTracking->COSTARICA = false;
                $historialTracking->CLIENTE = false;
            }

            $historialTracking->save();  // Guardamos el historial



            return response()->json([
                'message' => 'Historial creado exitosamente para el tracking.',
                'tracking' => $tracking,
                'historial' => $historialTracking,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Hubo un error al crear el historial del tracking: ' . $e->getMessage(),
            ], 400);
        }
    }

    public function actualizarEstadoTracking($idTracking, $tipo, $idDireccionNueva = null)
    {

        $tracking = Tracking::where('IDTRACKING', (string)$idTracking)->first();
        if (!$tracking) return false;

        $trackingViejo = clone $tracking;

        switch ($tipo) {
            case 1:
                $tracking->ENTREGADOCOSTARICA = true;
                break;
            case 2:
                $tracking->ENTREGADOCOSTARICA = true;
                $tracking->ENTREGADOCLIENTE = true;
                break;
            case 'desactivar_costarica':
                $tracking->ENTREGADOCOSTARICA = false;
                break;
            case 'desactivar_cliente':
                $tracking->ENTREGADOCLIENTE = false;
                break;
            default:
                return true;
        }

        // Manejo de fecha de entrega
        if ($tracking->ENTREGADOCLIENTE) {
            $tracking->FECHAENTREGA = now();
        } else {
            $tracking->FECHAENTREGA = null;
        }

        if ($idDireccionNueva != null)  $tracking->IDDIRECCION = $idDireccionNueva;

        // Solo enviar correo si se está activando
        if ($tipo == 1 || $tipo == 2) {

            $this->EnviaCorreos($tracking, $trackingViejo);
        }

        return $tracking->save();
    }


    public function EliminarHistorialPorTipo($data)
    {
        $tipo = $data->tipo; // 'costarica' o 'cliente'
        $columna = $tipo === 'costarica' ? 'COSTARICA' : 'CLIENTE';

        $tracking = Tracking::where('IDTRACKING', $data->idTracking)->first();
        if (!$tracking) {
            return false;
        }

        $historial = TrackingHistorial::where('IDTRACKING', $tracking->id)
            ->where($columna, true)
            ->first();

        if ($historial) {
            $historial->delete();
        }

        // Ahora actualizar el estado del tracking
        $tipoUpdate = $tipo === 'costarica' ? 'desactivar_costarica' : 'desactivar_cliente';
        return $this->actualizarEstadoTracking($data->idTracking, $tipoUpdate);
    }

    public function EliminarHistorial($data)
    {
        $historial = TrackingHistorial::find($data->idHistorial);

        if (!$historial) {
            return false;
        }

        return $historial->delete(); // o ->forceDelete() si querés eliminarlo permanentemente
    }

    public function RestaurarEliminados($idTracking)
    {
        try {
            $tracking = Tracking::withTrashed()->where('IDTRACKING', $idTracking)->first();
            if ($tracking->isEmpty()) {
                return false; // Si no se encuentra el tracking o el historial, retornar false
            }
            $historialTracking = TrackingHistorial::withTrashed()->where('IDTRACKING', $tracking->id)->get();
            if ($historialTracking->isEmpty()) {
                return false; // Si no se encuentra el tracking o el historial, retornar false
            }

            $historialTracking->each(function ($historial) {
                $historial->restore(); // Restaurar cada historial
            });
            $tracking->restore(); // Restaurar el tracking
            return true; // Retornar true si la restauración fue exitosa
        } catch (Exception $e) {
            throw new Exception('Error al restaurar el tracking: ' . $e->getMessage());
            return false; // Retornar false si ocurre un error
        }
    }
    public function EliminarTracking($idTracking)
    {

        try {

            $tracking = Tracking::where('IDTRACKING', $idTracking)->first();
            // var_dump($tracking);
            // die();
            $historialTracking = TrackingHistorial::where('IDTRACKING', $tracking->id)->get();
            $direccion = Direccion::where('ID', $tracking->IDDIRECCION)->first();

            $cliente = Cliente::where('ID', $direccion->IDCLIENTE)->first();
            $usuario = $cliente->usuario()->first();

            if (!$tracking || !$historialTracking || !$cliente) {
                return false;
            }

            // Eliminar los historiales asociados al tracking
            $historialTracking->each(function ($historial) {
                $historial->delete(); // Eliminar cada historial
            });
            // Eliminar el tracking
            $tracking->delete();

            if ($usuario && $usuario->email) {
                EventoCorreoEliminarTracking::dispatch($tracking, $usuario);
            }
            return true;
        } catch (Exception $e) {

            return response()->json([
                'error' => 'Hubo un error al eliminar el tracking: ' . $e->getMessage(),
            ], 400);
        }
    }
    public static function Filtro($request): ?LengthAwarePaginator
    {
        try {
            $query = $request->input('buscar');

            $trackings = Tracking::select(
                'tracking.id',
                'IDAPI',
                'IDTRACKING',
                'DESCRIPCION',
                'DESDE',
                'HASTA',
                'DESTINO',
                'COURIER',
                'DIASTRANSITO',
                'PESO',
                'IDDIRECCION',
                'tracking.IDUSUARIO',
                'ENTREGADOCLIENTE',
                'ENTREGADOCOSTARICA',
                'd.id',
                'c.IDUSUARIO'
            )
                ->join('direccion as d', 'tracking.IDDIRECCION', '=', 'd.id')
                ->join('cliente as c', 'd.IDCLIENTE', '=', 'c.id')
                ->join('users as u', 'c.IDUSUARIO', '=', 'u.id')
                ->orderBy('u.NOMBRE');

            if (!empty($query)) {
                $trackings->where(function ($q) use ($query) {
                    $q->where('DESCRIPCION', 'like', "%{$query}%")
                        ->orWhere('IDTRACKING', 'like', "%{$query}%")
                        ->orWhere('DESDE', 'like', "%{$query}%")
                        ->orWhere('DESTINO', 'like', "%{$query}%")
                        ->orWhere('COURIER', 'like', "%{$query}%")
                        ->orWhere('HASTA', 'like', "%{$query}%");
                });
            }

            if (Auth::user()->getNombrePerfil() == 'Cliente') {
                $cliente = Cliente::where('IDUSUARIO', Auth::user()->id)->first();
                $direcciones = Cliente::find($cliente->id)->direcciones;
                // Obtener IDs de las direcciones
                $idsDirecciones = $direcciones->pluck('id');
                $trackings->whereIn('IDDIRECCION', $idsDirecciones);
            }


            return $trackings->paginate(8);
        } catch (\Throwable $e) {
            Log::error('Error en ServicioUsuario::Filtro', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            return null;
        }
    }
    public function CalcularDashboardPedidos($entradosCliente)
    {
        try {
            // Determinar el campo de fecha según el tipo de cliente
            $campoValidacionFecha = $entradosCliente ? 'FECHAENTREGA' : 'created_at';

            // Cantidad de pedidos en tránsito actuales
            $cantidadPedidosActuales = Tracking::where('ENTREGADOCOSTARICA', $entradosCliente)
                ->where('ENTREGADOCLIENTE', $entradosCliente)
                ->count();

            // Fecha de inicio y fin del mes pasado
            $inicioMesPasado = now()->subMonth()->startOfMonth();
            $finMesPasado = now()->subMonth()->endOfMonth();

            // Cantidad de pedidos en tránsito durante el mes pasado
            $cantidadPedidosMesPasado = Tracking::where('ENTREGADOCOSTARICA', $entradosCliente)
                ->where('ENTREGADOCLIENTE', $entradosCliente)
                ->where($campoValidacionFecha, '>=', $inicioMesPasado)
                ->where($campoValidacionFecha, '<=', $finMesPasado)
                ->where(function ($query) use ($finMesPasado) {
                    $query->whereNull('deleted_at')
                        ->orWhere('deleted_at', '>', $finMesPasado);
                })
                ->count();
            $TEMPCantidadPedidosMesPasado = $cantidadPedidosMesPasado;
            // Evitar división por cero y ajustar en caso de que no haya pedidos en el mes pasado
            if ($cantidadPedidosMesPasado == 0) {
                $TEMPCantidadPedidosMesPasado = 1;  // O podrías optar por 0 si quieres mostrar un cambio de 100% o un valor diferente.
            }

            // Calcular el porcentaje de cambio entre este mes y el mes pasado
            $porcentajeCambio = (($cantidadPedidosActuales - $cantidadPedidosMesPasado) / $TEMPCantidadPedidosMesPasado) * 100;

            // Retornar los datos calculados
            return [
                'pedidosActuales' => $cantidadPedidosActuales,
                'porcentajeMesPasado' => round($porcentajeCambio, 2),  // Redondear a dos decimales
            ];
        } catch (Exception $e) {
            // Manejo de errores
            throw new \Exception('Error al calcular los pedidos en tránsito: ' . $e->getMessage());
        }
    }




    public function PromedioPedidosXCliente()
    {
        try {
            $inicioMesActual = now()->startOfMonth();
            $inicioMesAnterior = now()->subMonth()->startOfMonth();
            $finMesAnterior = now()->subMonth()->endOfMonth();

            $cantidadPedidosMesActual = Tracking::where('created_at', '>=', $inicioMesActual)
                ->whereNull('deleted_at')
                ->count();

            $cantidadPedidosMesAnterior = Tracking::where('created_at', '>=', $inicioMesAnterior)
                ->where('created_at', '<=', $finMesAnterior)
                ->whereNull('deleted_at')
                ->count();

            // Evitar la división por cero en cantidad de clientes activos
            $cantidadClientesActivos = Cliente::whereNull('deleted_at')->count();

            $promedioMesActual = $cantidadClientesActivos > 0
                ? $cantidadPedidosMesActual / $cantidadClientesActivos
                : 1; // Evitar división por cero usando 1

            $promedioMesAnterior = $cantidadClientesActivos > 0
                ? $cantidadPedidosMesAnterior / $cantidadClientesActivos
                : 1; // Evitar división por cero usando 1

            // Calcular la diferencia entre promedios
            $diferenciaPromedio = $promedioMesActual - $promedioMesAnterior;

            return [
                'promedioMesActual' => round($promedioMesActual, 2),
                'porcentajeDiferencia' => round($diferenciaPromedio, 2),
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error al calcular los pedidos por cliente: ' . $e->getMessage());
        }
    }
    private function findExistingFile($pattern)
    {
        $directory = storage_path('app/public/facturas');  // Ruta donde se almacenan los archivos

        // Obtener todos los archivos en la carpeta 'facturas'
        $files = glob($directory . '/' . $pattern . '*');  // Buscar archivos que comiencen con el patrón

        // Si se encontró algún archivo
        if (count($files) > 0) {
            return basename($files[0]); // Retorna el primer archivo encontrado
        }

        // Si no se encontró ningún archivo
        return null;
    }
    private function ProcesoFactura($file, $idTracking, $eliminarFactura)
    {


        $fileUrl = null;
        $tracking = Tracking::where("IDTRACKING", $idTracking)->first();

        if ($file) {

            // Buscar el archivo a eliminar con el mismo idTracking
            $existingFilePattern = $idTracking;  // Usamos el idTracking para buscar el archivo

            // Buscar el archivo anterior con el patrón basado en idTracking
            $existingFilePath = $this->findExistingFile($existingFilePattern);

            // Si encontramos el archivo anterior, lo eliminamos
            if ($existingFilePath) {
                // Eliminar el archivo anterior
                $filePath = storage_path('app/public/facturas/' . $existingFilePath);

                if (file_exists($filePath)) {
                    $fileUrl = null;
                    unlink($filePath); // Elimina el archivo
                }
            }

            // Obtener la extensión del archivo
            $extension = $file->getClientOriginalExtension();

            // Crear un nombre de archivo único concatenando el idTracking y el timestamp
            $fileName = $idTracking . '.' . $extension;

            // Guardar el archivo en el almacenamiento público
            $filePath = $file->storeAs('facturas', $fileName, 'public');

            // Obtener la URL pública del archivo
            $fileUrl = 'app/public/' . $filePath;
            EventoFacturaGenerada::dispatch($tracking, false);
        } else {
            // Si no se sube un nuevo archivo y se marca la opción de eliminar, procedemos a eliminar el archivo
            if ($eliminarFactura) {

                // Buscar y eliminar el archivo anterior
                $existingFilePattern = $idTracking;
                $existingFilePath = $this->findExistingFile($existingFilePattern);

                if ($existingFilePath) {
                    // Eliminar el archivo
                    $filePath = storage_path('app/public/facturas/' . $existingFilePath);
                    EventoFacturaGenerada::dispatch($tracking, true);


                    unlink($filePath); // Elimina el archivo
                    $fileUrl = null;
                }
            }
        }

        return $fileUrl;
    }
    public function GuardarTracking(array $data): bool
    {
        try {
            DB::beginTransaction();

            $encabezado = $data['encabezado'];
            $detalle = $data['detalle'] ?? [];

            $rutaFactura = $this->ProcesoFactura($data['factura'], $encabezado['IDTRACKING'], $data['eliminarFactura']);

            $tracking = new Tracking();
            $tracking->IDAPI = 0;
            $tracking->IDTRACKING = $encabezado['IDTRACKING'] ?? null;
            $tracking->DESCRIPCION = $encabezado['DESCRIPCION'] ?? '';
            $tracking->PESO = $encabezado['PESO'] ?? 0.000;
            $tracking->IDDIRECCION = $encabezado['IDDIRECCION'] ?? 1;
            $tracking->ENTREGADOCOSTARICA = $encabezado['COSTARICA'] ?? false;
            $tracking->ENTREGADOCLIENTE = $encabezado['ENTREGADOCLIENTE'] ?? false;
            $tracking->DESDE = $encabezado['DESDE'] ?? '';
            $tracking->HASTA = $encabezado['HASTA'] ?? '';
            $tracking->DESTINO = $encabezado['DESTINO'] ?? '';
            $tracking->COURIER = $encabezado['COURIER'] ?? '';
            $tracking->DIASTRANSITO = $encabezado['DIASTRANSITO'] ?? 0;
            $tracking->IDUSUARIO = Auth::user()->id ?? 1;
            $tracking->RUTAFACTURA = $rutaFactura ?? NULL;
            $tracking->save();

            foreach ($detalle as $evento) {
                $historial = new TrackingHistorial();
                $historial->IDTRACKING = $tracking->id;
                $historial->DESCRIPCION = $evento['DESCRIPCION'] ?? null;
                $historial->DESCRIPCIONMODIFICADA = $evento['DESCRIPCIONMODIFICADA'] ?? '';
                $historial->FECHA = isset($evento['FECHA']) ? Carbon::parse($evento['FECHA']) : now();
                $historial->IDCOURIER = $evento['IDCOURIER'] ?? 0;
                $historial->OCULTADO = $evento['OCULTADO'] ?? false;
                $historial->PAISESTADO = $evento['PAISESTADO'] ?? '';
                $historial->TIPO = $evento['TIPO'] ?? 1;
                $historial->CODIGOPOSTAL = $evento['CODIGOPOSTAL'] ?? 0;
                $historial->save();
            }
            //Para los filtros
            $this->servicioDireccionesTracking->CrearDireccionTracking($tracking->DESDE);
            $this->servicioDireccionesTracking->CrearDireccionTracking($tracking->HASTA);
            $this->servicioDireccionesTracking->CrearDireccionTracking($tracking->DESTINO);

            DB::commit();
            $usuario = $this->ObtenerCliente($tracking);

            if (!empty($usuario)) {

                EventoClienteEnlazadoPaquete::dispatch($tracking, $usuario);
                $this->EnviaCorreosEnlazado($tracking);
            }
            $rutaFactura = $this->ProcesoFactura($data['factura'], $encabezado['IDTRACKING'], $data['eliminarFactura']);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar tracking: ' . $e->getMessage());
            throw new \Exception('Error al guardar el tracking: ' . $e->getMessage());
        }
    }
}
