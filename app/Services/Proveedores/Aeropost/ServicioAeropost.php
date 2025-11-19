<?php

namespace App\Services\Proveedores\Aeropost;

use App\Actions\ActionDepurarRegex;
use App\Exceptions\ExceptionAeropost;
use App\Exceptions\ExceptionAPCourierNoObtenido;
use App\Exceptions\ExceptionAPCouriersNoObtenidos;
use App\Exceptions\ExceptionAPObtenerPaquetes;
use App\Exceptions\ExceptionAPRequestActualizarPrealerta;
use App\Exceptions\ExceptionAPRequestEliminarPrealerta;
use App\Exceptions\ExceptionAPTokenNoObtenido;
use App\Models\Cliente;
use App\Models\Enum\TipoHistorialTracking as EnumTipoHistorialTracking;
use App\Models\Enum\TipoImagen;
use App\Models\Imagen;
use App\Models\Prealerta;
use App\Models\Tracking;
use App\Models\TrackingHistorial;
use App\Models\TrackingProveedor;
use App\Models\User;
use App\Services\Proveedores\InterfazProveedor;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServicioAeropost implements InterfazProveedor
{

    private AeropostApiClient $apiClient;

    public function __construct()
    {
        $this->apiClient = new AeropostApiClient(false);
    }

    /**
     * @throws ConnectionException
     * @throws ExceptionAPCouriersNoObtenidos
     * @throws ExceptionAPTokenNoObtenido
     * @throws ExceptionAPCourierNoObtenido|ExceptionAeropost
     */
    public function RegistrarPrealerta($tracking, $valor, $descripcion, $idProveedor): Prealerta
    {
        // 3. Obtenemos el courier de nuestro idTracking gracias al regex
        // 4. Al obtener el courier, nombreTienda va a ser: Tienda de {courier}
        // 5. Enviar el request para crear la prealerta
        // 5.1. Si da error 500, hacer el req de solicitar paquete para ver si nos envio un falso positivo (nos dice que no se guardo cuando si, esto lo hace RequestRegistrarPrealerta)
        // 6. Crear/Actualizar el trackingProveedor
        // 7. Crear/Actualizar la prealerta

        // 3. Obtenemos el courier de nuestro idTracking gracias al regex
        $courierSeleccionado = self::ObtenerCourier($tracking->IDTRACKING);

        // 4. Al obtener el courier, nombreTienda va a ser: Tienda de {courier}
        $nombreTienda = 'Tienda de ' . $courierSeleccionado['name'];

        // 5. Enviar el request para crear la prealerta
        try {
            $idPrealerta = $this->apiClient->RegistrarPrealerta(3861094, $courierSeleccionado['id'], $tracking->IDTRACKING, $nombreTienda, $valor, $descripcion);
        } catch (Exception $e) {
            // # Como a veces el API de AP de registrar prealerta falla, verificar con otro endpoint y obtener el idPrealerta
            $dataGetPackage = $this->apiClient->ObtenerPaquete($tracking->IDTRACKING);
            Log::info('[ServicioAeropost,RP] campo noteId: ' . $dataGetPackage['noteId']);
            $idPrealerta = $dataGetPackage['noteId'];
        }
        //Log::info('[ServicioAeropost,RP] idPrealerta ' . $idPrealerta);

        // 6. Crear/Actualizar el trackingProveedor
        if ($tracking->trackingProveedor)
            $trackingProveedor = $tracking->trackingProveedor;
        else
            $trackingProveedor = new TrackingProveedor;
        $trackingProveedor->IDTRACKING = $tracking->id;
        $trackingProveedor->IDPROVEEDOR = $idProveedor;
        $trackingProveedor->save();

        // 7. Crear/Actualizar la prealerta
        if ($trackingProveedor->prealerta)
            $prealerta = $trackingProveedor->prealerta;
        else
            $prealerta = new Prealerta;
        $prealerta->DESCRIPCION = $descripcion;
        $prealerta->VALOR = $valor;
        $prealerta->NOMBRETIENDA = $nombreTienda;
        $prealerta->IDCOURIER = $courierSeleccionado['id'];
        $prealerta->IDPREALERTA = $idPrealerta;
        $prealerta->IDTRACKINGPROVEEDOR = $trackingProveedor->id;
        $prealerta->save();

        return $prealerta;
    }

    /**
     * @throws ExceptionAPRequestActualizarPrealerta
     * @throws ExceptionAPTokenNoObtenido
     * @throws ConnectionException
     */
    public function ActualizarPrealerta($idPrealerta, $descripcion, $valor, $numeroTracking, $nombreTienda, $courierId, $consigneeId): void
    {
        $this->apiClient->ActualizarPrealerta($idPrealerta, $descripcion, $valor, $numeroTracking, $nombreTienda, $courierId, $consigneeId);
    }

    /**
     * @throws ExceptionAPRequestEliminarPrealerta
     * @throws ExceptionAPTokenNoObtenido
     * @throws ConnectionException
     */
    public function EliminarPrealerta($idPrealerta): void
    {
        $this->apiClient->EliminarPrealerta($idPrealerta);
    }

    /**
     * @param string $idTracking
     * @return array
     * @throws ExceptionAPCourierNoObtenido
     * @throws ExceptionAPCouriersNoObtenidos
     * @throws ConnectionException
     * @throws ExceptionAPTokenNoObtenido
     */
    public function ObtenerCourier($idTracking): array
    {
        // 1. Obtenemos los couriers
        // 2. Recorremos los couriers
        // 3. Obtenemos los regex de cada courier y los comparamos con el idTracking
        // 4. Si cumple con alguno, se retorna t#do el array asociativo
        // 5. Si no cumple con ninguno, obtener el courier que es Other (en los docs el default es el courierId 0)
        $courierSeleccionado = null;

        // 1. Obtenemos los couriers
        $couriers = $this->apiClient->ObtenerCouriers();

        // 2. Recorremos los couriers
        foreach ($couriers as $courier) {
            // 3. Obtenemos los regex de cada courier y los comparamos con el idTracking
            $regexActual = $courier['regex'];

            // - Se excluye el courier de id 0 => Other/Default que no tiene el campo de regex
            if ($courier['id'] == 0 || $courier['id'] == 9) {
                continue;
            }

            if (!$regexActual) {
                throw new ExceptionAPCourierNoObtenido('El elemento courier no contiene el campo regex');
            }

            // 4. Si con alguno, se retorna t#do el array asociativo
            // - Antes de hacer el preg_match necesito agregarle a ambos lados
            $regexDepurado = ActionDepurarRegex::execute($regexActual);

            if (preg_match($regexDepurado, $idTracking)) {
                $courierSeleccionado = $courier;
                break;
            }
        }

        // 5. Si no cumple con ninguno, obtener el courier que es Other/Default (en los docs el default es el courierId 0)
        if (!$courierSeleccionado) {
            $courierDefault = null;

            foreach ($couriers as $courier) {
                $courierId = $courier['id'];

                if (!isset($courierId)) { //isset porque si solo pongo !$courierId 0 me lo toma como false y entra cuando no deberia
                    Log::info('[ServicioAeropost,OC] El campo id en un elemento de courier no existe' . json_encode($couriers));
                    throw new ExceptionAPCourierNoObtenido('El campo id en un elemento de courier no existe');
                }
                if ($courierId == 0 || $courier['id'] == 9) {
                    $courierDefault = $courier;
                    break;
                }
            }

            if (!$courierDefault) {
                Log::info('[ServicioAeropost,OC] No se encontro el courier default');
                throw new ExceptionAPCourierNoObtenido('No se encontro el courier default');
            }
            $courierSeleccionado = $courierDefault;
        }

        return $courierSeleccionado;
    }

    /**
     * @param array $numerosTracking
     * @return void
     * @throws ExceptionAPObtenerPaquetes
     */
    public function SincronizarEncabezadoTrackings(array $numerosTracking): void
    {
        // - Si se envian numeros de tracking se interpreta como que solo se desean actualizar esos numeros de tracking.
        // 1. Verificar si se pasaron numeros de tracking
        // 2. Si no se pasaron numeros de tracking, entonces llamamos todos los paquetes de aeropost
        // 3. Sincronizar los encabezados de los trackings
        // 3.1. Obtener todos los paquetes que hay en Aeropost
        // 4. Crear un mapa de trackingAeropost para buscar en O(1)
        // 5. Si sobran trackings, hay que crearlos nuevos con el clienteDefault

        // 1. Verificar si se pasaron numeros de tracking
        if ($numerosTracking == null || count($numerosTracking) == 0) {
            // 2. Si no se pasaron numeros de tracking, entonces obtener todos los paquetes en estados distintos a EDO y FDO y con idProveedor != a ML
            $trackingProveedorIds = TrackingProveedor::where('IDPROVEEDOR', '!=', 2)
                ->pluck('IDTRACKING');

            // Filtramos listadoPendientes directamente en la consulta
            $trackings = Tracking::where(function($q) use ($trackingProveedorIds) {
                $q->whereIn('id', $trackingProveedorIds)
                    ->whereNotIn('ESTADOSINCRONIZADO', ['Entregado', 'Facturado']);
            })
                ->orWhere('ESTADOSINCRONIZADO', 'Sin Prealertar') //porque no trae los sin Prealertar. RAZON: Si un paquete se SPR y despues AP me lo trae, aqui no agarro ese tracking, entonces al registrar me dara error
                ->get();
            $numerosTracking = [];
        } else {

            $trackings = Tracking::with('trackingProveedor') //porque se usa trackingProveedor en encabezado
                ->whereIn('IDTRACKING', $numerosTracking)
                ->get();
        }

        // 3. Sincronizar los encabezados de los trackings
        // 3.1. Obtener todos los paquetes que hay en Aeropost
        $trackingsAeropost = $this->apiClient->ObtenerPaquetesMasivos($numerosTracking, true);

        Log::info('[SA, SET] CANTIDAD TODOS: ' . count($trackingsAeropost));

        DB::transaction(function () use ($trackings, $trackingsAeropost) {

            // 4. Crear un mapa de trackingAeropost para buscar en O(1)
            $map = [];

            foreach ($trackingsAeropost as $trackingAeropost) {
                if (!empty($trackingAeropost['courierTracking'])) {
                    $map[$trackingAeropost['courierTracking']] = $trackingAeropost;
                }
                else if (!empty($trackingAeropost['aerotrack'])) {
                    $map[$trackingAeropost['aerotrack']] = $trackingAeropost;
                }
            }

            // Recorrer trackings de BD y actualizar si existe en el mapa
            $revisados = 0;
            foreach ($trackings as $trackingBd) {

                $id = $trackingBd->IDTRACKING;

                if (isset($map[$id])) {

                    //validar si los campos son iguales
                    if($this->CambiosEncabezado($trackingBd ,$map[$id])){
                        $revisados++;
                        $this->ActualizarEncabezadoTracking($trackingBd, $map[$id]);
                    }

                    // eliminar del mapa para saber qué no se usó
                    unset($map[$id]);
                }
            }
            // Lo que queda en el mapa → son nuevos
            Log::info('CANTIDAD SIN REG: ' . count($map) . ' REVISADOS: ' . $revisados);

            // 5. Si sobran trackings, hay que crearlos nuevos con el clienteDefault
            $this->RegistrarTrackingsNoExistentes(array_values($map));
        });

    }

    /**
     * @param array $numerosTracking
     * @param bool $fechaAyer
     * @return void
     * @throws ExceptionAPObtenerPaquetes
     */
    public function SincronizarCompletoTrackings(array $numerosTracking, bool $fechaAyer = true): void
    {
        // - Si $fechaAyer es false, se importan todos los paquetes
        // - Si se envian numeros de tracking se interpreta como que solo se desean actualizar esos numeros de tracking.
        // 1. Verificar si se pasaron numeros de tracking
        // 2. Si no se pasaron numeros de tracking, entonces llamamos todos los paquetes de aeropost
        // 3. Sincronizar completo los trackings
        // 3.1. Obtener todos los paquetes que hay en Aeropost
        // 3.2. Actualizar el encabezado del tracking
        // 3.3. Actualizar los attachments
        // 3.4. Actualizar los historiales
        // 4. Si sobran trackings, hay que crearlos nuevos con el clienteDefault

        // 1. Verificar si se pasaron numeros de tracking
        if ($numerosTracking == null || count($numerosTracking) == 0) {
            // 2. Si no se pasaron numeros de tracking, entonces obtener todos los paquetes en estados distintos a EDO y FDO y con idProveedor != a ML
            $trackingProveedorIds = TrackingProveedor::where('IDPROVEEDOR', '!=', 2)
                ->pluck('IDTRACKING');

            // Filtramos listadoPendientes directamente en la consulta
            $trackings = Tracking::whereNotIn('ESTADOSINCRONIZADO', ['Entregado', 'Facturado'])
                ->whereIn('id', $trackingProveedorIds)
                ->get();
            $numerosTracking = [];
        } else {
            $trackings = Tracking::with('trackingProveedor') //porque se usa trackingProveedor en encabezado
            ->whereIn('IDTRACKING', $numerosTracking)
                ->get();
        }

        // 3. Sincronizar completo los trackings
        // 3.1. Obtener todos los paquetes que hay en Aeropost
        $trackingsAeropost = $this->apiClient->ObtenerPaquetesMasivos($numerosTracking, $fechaAyer);
        Log::info('[SA, SCT] CANTIDAD TODOS: ' . count($trackingsAeropost));

        DB::transaction(function () use ($trackings, &$trackingsAeropost) {
            //recorro todos los trackings de la bd
            foreach ($trackings as $trackingBd) {

                //recorro todos los trackings del request
                foreach ($trackingsAeropost as $key => $trackingAeropost) {
                    if ($trackingAeropost['courierTracking'] == $trackingBd->IDTRACKING || $trackingAeropost['aerotrack'] == $trackingBd->IDTRACKING) {
                        // 3.2. Actualizar el encabezado del tracking
                        $this->ActualizarEncabezadoTracking($trackingBd, $trackingAeropost);

                        // 3.3. Actualizar los attachments
                        $this->ActualizarAttachments($trackingBd, $trackingAeropost);

                        // 3.4. Actualizar los historiales
                        $this->ActualizarHistoriales($trackingBd, $trackingAeropost);

                        // eliminar el trackingAeropost que ya se procesó
                        unset($trackingsAeropost[$key]);
                        break;
                    }

                }
            }

            Log::info('CANTIDAD SIN REG: ' . count($trackingsAeropost));
            // 4. Si sobran trackings, hay que crearlos nuevos con el clienteDefault
            $this->RegistrarTrackingsNoExistentes($trackingsAeropost);
        });
    }

    /**
     * La funcion se encarga de, una vez obtenido el tracking match con el tracking de Aeropost, actualizar los datos
     * @param Tracking $trackingBd
     * @param array $trackingAeropost
     * @return void
     * @throws ExceptionAPObtenerPaquetes
     */
    private function ActualizarEncabezadoTracking(Tracking $trackingBd, array $trackingAeropost): void
    {
        // 1. Validar que el estado sea != null.
        // 2. Actualizar estado
        // 3. Actualizar el peso
        // 4. Actualizar el aeroTrack

        $estado = $this->mapEstadoAeropost($trackingAeropost['graphicStationID']);
        $aerotrack = Arr::get($trackingAeropost, 'aerotrack');
        $weightKilos = Arr::get($trackingAeropost, 'weightKilos');

        if ($estado == null) {
            throw new ExceptionAPObtenerPaquetes($trackingBd->IDTRACKING);
        }

        // 2. Actualizar estado (Si es EN o FDO solo editar el sincronizado)
        if ($trackingBd->ESTADOSINCRONIZADO == $trackingBd->ESTADOMBOX || $trackingBd->ESTADOMBOX != 'Entregado' || $trackingBd->ESTADOMBOX != 'Facturado') {
            $trackingBd->ESTADOMBOX = $estado;
        }
        $trackingBd->ESTADOSINCRONIZADO = $estado;

        // 3. Actualizar el peso
        if (blank($weightKilos)) {
            throw new ExceptionAPObtenerPaquetes($trackingBd->IDTRACKING);
        }
        $trackingBd->PESO = $weightKilos;

        $trackingBd->save();

        // 4. Actualizar el aeroTrack
        if ($aerotrack) {
            $trackingProveedor = $trackingBd->trackingProveedor;
            $trackingProveedor->TRACKINGPROVEEDOR = $aerotrack;
            $trackingProveedor->save();
        }

    }

    /**
     * @param Tracking $trackingBd
     * @param array $trackingAeropost
     * @return void
     */
    private function ActualizarAttachments(Tracking $trackingBd, array $trackingAeropost): void
    {

        $imagenesAeropost = Arr::get($trackingAeropost, 'attachments');
        if (count($imagenesAeropost) <= 0)
            return;

        foreach ($imagenesAeropost as $imagen) {
            if ($imagen['category'] != 3)
                continue;
            //solo si son category == 3 porque esas son las fotos del paquete
            Imagen::firstOrCreate(
                [
                    'RUTA' => $imagen['url'],
                ],        // Criterios de búsqueda
                [
                    'TIPOIMAGEN' => TipoImagen::Aeropost->value,
                    'RUTA' => $imagen['url'],
                    'IDTRACKING' => $trackingBd->id
                ]    // Atributos para crear si no existe
            );
        }
    }

    /**
     * @param Tracking $trackingBd
     * @param array $trackingAeropost
     * @return void
     * @throws ExceptionAPObtenerPaquetes
     */
    private function ActualizarHistoriales(Tracking $trackingBd, array $trackingAeropost): void
    {
        // 1. Validar que el trackingAeropost tenga un aerotrack
        // 2. Llamar al endpoint para obtener los historiales
        // 3. Crear los nuevos historiales

        $aerotrack = Arr::get($trackingAeropost, 'aerotrack');

        if ($aerotrack) {
            $historialesAp = $this->apiClient->obtenerHistorial($aerotrack, $trackingBd->IDTRACKING);
            $this->nuevosHistoriales($trackingBd, $historialesAp);
        }
    }

    private function RegistrarTrackingsNoExistentes(array $trackingsAeropost): void
    {
        // - Los no existentes no se hace el llamado a parcelsApp, eso se espera a que le de  al detalle
        // 1. Obtiene el cliente "NoAsignado" para asignar trackings nuevos.
        // 2. Mapea el estado de cada tracking y construye tres arreglos:
        //    - $dataTracking: para insertar los registros base de Tracking.
        //    - $dataTrackingProveedor: para vincular el tracking con el proveedor.
        //    - $dataPrealerta: para crear la prealerta asociada.
        // 3. Inserta todos los Trackings en un solo query.
        // 4. Recupera los IDs generados de cada Tracking recién insertado.
        // 5. Usa esos IDs para armar e insertar masivamente los registros de:
        //    - TrackingProveedor
        //    - Prealerta
        // 6. T#do se realiza en tres queries principales para optimizar rendimiento:
        //    - Insert masivo de Tracking
        //    - Insert masivo de TrackingProveedor
        //    - Insert masivo de Prealerta

        $user = User::where('APELLIDOS', 'NoAsignado')
            ->first();
        $cliente = $user->cliente;
        $dataTracking = [];

        // 2. Llenar el tracking, prealerta, y trackingProveedor
        foreach ($trackingsAeropost as $trackingAeropost) {
            $estado = $this->mapEstadoAeropost($trackingAeropost['graphicStationID']);

            if ($estado === null) {
                throw new ExceptionAPObtenerPaquetes($trackingAeropost['courierTracking']);
            }

            //si el courierTracking viene vacio, ponerle IDTRACKING el AEROTRACK
            if ($trackingAeropost['courierTracking'] == '') {
                $idTracking = $trackingAeropost['aerotrack'];
                //Log::info('[RTNE] COURIER VACIO' . json_encode($trackingAeropost));
            } else
                $idTracking = $trackingAeropost['courierTracking'];

            // Armar array de Trackings
            $dataTracking[] = [
                'IDAPI' => 0,
                'IDTRACKING' => $idTracking,
                'DESCRIPCION' => $trackingAeropost['description'],
                'DESDE' => '',
                'HASTA' => '',
                'DESTINO' => '',
                'COURIER' => 'Aeropost',
                'DIASTRANSITO' => 0,
                'PESO' => $trackingAeropost['weightKilos'],
                'IDDIRECCION' => $cliente->direccionPrincipal->id,
                'IDUSUARIO' => Auth::id() ?? 1,
                'ESTADOMBOX' => $estado,
                'ESTADOSINCRONIZADO' => $estado,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 3. Insertar los trackings masivamente
        Tracking::insert($dataTracking);

        // 4. Obtener los IDs recién insertados usando IDTRACKING único
        $ids = Tracking::whereIn('IDTRACKING', array_column($dataTracking, 'IDTRACKING'))
            ->pluck('id', 'IDTRACKING')
            ->toArray();

        // 5. Armar TrackingProveedor y Prealerta con los IDs correctos
        $dataPre = [];
        foreach ($trackingsAeropost as $trackingAeropost) {
            $trackingId = $ids[$trackingAeropost['courierTracking']]
                ?? $ids[$trackingAeropost['aerotrack']]
                ?? null;

            $trackingProveedor = trim($trackingAeropost['aerotrack']) !== ''
                ? $trackingAeropost['aerotrack']
                : null;

            if (!$trackingId) {
                Log::info('[SA, RTNE] No SE encontro el trackingid' . json_encode($trackingAeropost));
                continue; // seguridad por si no se encuentra
            }

            // TrackingProveedor
            $trackingProveedor = TrackingProveedor::create([
                'IDTRACKING' => $trackingId,
                'IDPROVEEDOR' => 1,
                'TRACKINGPROVEEDOR' => $trackingProveedor,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Prealerta
            $dataPre[] = [
                'DESCRIPCION' => $trackingAeropost['description'],
                'VALOR' => $trackingAeropost['declaredValue'],
                'IDCOURIER' => 0,
                'NOMBRETIENDA' => 'TIENDA DE',
                'IDTRACKINGPROVEEDOR' => $trackingProveedor->id,
                'IDPREALERTA' => $trackingAeropost['noteId'] != 0 ? $trackingAeropost['noteId'] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 6. Insertar masivamente Prealerta
        Prealerta::insert($dataPre);

    }

    /**
     * @param Tracking $trackingBd
     * @param array $trackingAeropost
     * @return bool
     */
    private function CambiosEncabezado(Tracking $trackingBd, array $trackingAeropost): bool{
        // - Si algun campo es distinto, pasar true, sino false
        // 1. Validar los campos del encabezado

        $estado = $this->mapEstadoAeropost($trackingAeropost['graphicStationID']);
        $aerotrack = Arr::get($trackingAeropost, 'aerotrack');
        $weightKilos = Arr::get($trackingAeropost, 'weightKilos');

        if ($estado == null) {
            throw new ExceptionAPObtenerPaquetes($trackingBd->IDTRACKING);
        }

        if (blank($weightKilos)) {
            throw new ExceptionAPObtenerPaquetes($trackingBd->IDTRACKING);
        }

        //Log::info('TrackingAP: estado->'. $estado . ' aerotrack->'. $aerotrack . ' kilos->'. $weightKilos . ' TrackingBD: estado->'. $trackingBd->ESTADOSINCRONIZADO . ' aerotrack->'. $trackingBd->trackingProveedor->TRACKINGPROVEEDOR . ' peso->' . $trackingBd->PESO);

        // 1. Validar los campos del encabezado
        if ($estado == $trackingBd->ESTADOSINCRONIZADO
            && round($weightKilos, 3) == $trackingBd->PESO //3 es la precision
        ) {
            //revisar el aerotrack (se hace por separado por si el paquete que viene es una prealerta)
            if ($aerotrack &&
                $trackingBd->trackingProveedor->TRACKINGPROVEEDOR == $aerotrack
            ) {
                return false;
            }else
                return true;

        }else
            return true;


    }

    /**
     * Parsea una fecha de la API a Carbon, devolviendo null si falla.
     *
     * @param string $fecha
     * @return Carbon|null
     */
    private static function parseFecha(string $fecha): ?Carbon
    {
        $dt = Carbon::parse($fecha);
        $dt->setMicrosecond(0); // elimina milisegundos
        return $dt;
    }

    /**
     * Mapeo de estados según graphicStationId
     *
     * @param int $graphicStationId
     * @return string|null
     */
    private static function mapEstadoAeropost(int $graphicStationId): ?string
    {
        return match ($graphicStationId) {
            -1 => 'No se encontró',
            0 => 'Prealertado',
            3 => 'Recibido Miami',
            4 => 'Tránsito a CR',
            5, 6, 7 => 'Proceso Aduanas',
            8 => 'Oficinas MB',
            default => null,
        };
    }

    /**
     * Persiste entradas de historial nuevas (según la última fecha guardada).
     *
     * @param Tracking $tracking
     * @param array $historiasAp
     * @return void
     * @throws ExceptionAPObtenerPaquetes
     */
    private static function nuevosHistoriales(Tracking $tracking, array $historiasAp): void
    {
        // última fecha registrada para este tracking
        $ultima = $tracking->fechaUltimoHistorial();
        $ultimaAt = $ultima ? Carbon::parse($ultima) : Carbon::now()->subYear(); //$tracking->created_at->copy()->startOfDay() no sirve porque si se actualiza solo el encabezado, esta fecha estara despues
        $historiasAp = array_reverse($historiasAp); //esto porque las fechas de los historiales vienen en orden descendente, y la logica esta para que sea ascendente

        foreach ($historiasAp as $state) {

            $fechaStr = Arr::get($state, 'date');
            $status = Arr::get($state, 'status');
            $gateway = Arr::get($state, 'gatewayDescription');

            if (!$fechaStr || !$status) {
                throw new ExceptionAPObtenerPaquetes($tracking->IDTRACKING, null, null, 'No vino la fecha correcta ni el status correcto.');
            }

            $fechaAt = self::parseFecha($fechaStr);

            if ($fechaAt && (!$ultimaAt || $fechaAt->gt($ultimaAt))) {
                $historial = new TrackingHistorial();
                $historial->DESCRIPCION = Arr::get($status, 'description', '');
                $historial->DESCRIPCIONMODIFICADA = '';
                $historial->PAISESTADO = $gateway ?? '';
                $historial->CODIGOPOSTAL = 99999;
                $historial->OCULTADO = false;
                $historial->TIPO = EnumTipoHistorialTracking::AEROPOST->value;
                $historial->created_at = $fechaAt->format('Y-m-d H:i:s');
                $historial->updated_at = $fechaAt->format('Y-m-d H:i:s');
                $historial->IDCOURIER = $tracking->courrierNombreAId('Aeropost');
                $historial->IDTRACKING = $tracking->id;
                $historial->save();
                $ultimaAt = $fechaAt;
            }
        }
    }

}
