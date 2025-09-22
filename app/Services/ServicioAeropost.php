<?php

namespace App\Services;

use App\Actions\ActionDepurarRegex;
use App\Exceptions\ExceptionAeropost;
use App\Exceptions\ExceptionAPCourierNoObtenido;
use App\Exceptions\ExceptionAPCouriersNoObtenidos;
use App\Exceptions\ExceptionAPRequestActualizarPrealerta;
use App\Exceptions\ExceptionAPRequestEliminarPrealerta;
use App\Exceptions\ExceptionAPRequestRegistrarPrealerta;
use App\Exceptions\ExceptionAPTokenNoObtenido;
use App\Models\Prealerta;
use App\Models\Tracking;
use App\Models\TrackingProveedor;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\TrackingHistorial;
use App\Models\Enum\TipoHistorialTracking as EnumTipoHistorialTracking;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;


class ServicioAeropost
{


    /**
     * Procesa y sincroniza múltiples trackings contra la API de Aeropost:
     * - Obtiene token de acceso (si falla, registra y termina).
     * - Consulta detalle del paquete por IDTRACKING.
     * - Actualiza estado local (ESTADOSINCRONIZADO/ESTADOMBOX) y TrackingProveedor.
     * - Descarga y persiste historial de estados (solo entradas nuevas).
     *
     * @param array<int,string> $idsTracking  Lista de IDTRACKING (códigos externos Aeropost).
     *
     * @return void
     *
     * @throws QueryException Se propaga si ocurre un error de BD fuera de los try-catch internos.
     */
    public static function ProcesarTrackingsAeropost(array $idsTracking): void
    {
        if (empty($idsTracking)) {
            Log::info('ProcesarTrackingsPrealertados: lista de IDs vacía.');
            return;
        }

        // 1) Autenticación + Http client
        $baseUrl = rtrim((string) env('AEROPOST_URL_BASE'), '/');
        $http    = self::buildHttpClient();

        // 2) Procesar en bloques
        foreach (array_chunk($idsTracking, 50) as $chunk) {
            foreach ($chunk as $idTracking) {
                if (!is_string($idTracking) || $idTracking === '') {
                    Log::warning('IDTRACKING inválido; se ignora.', ['id' => $idTracking]);
                    continue;
                }
                try {
                    self::procesarTrackingId($idTracking, $baseUrl, $http);
                } catch (\Throwable $e) {
                    Log::error('Error procesando tracking de Aeropost.', [
                        'id' => $idTracking,
                        'ex' => $e->getMessage(),
                    ]);
                    // Continuamos con los demás
                }
            }
        }
    }

    /**
     * Procesa un IDTRACKING concreto: detalle, actualización y su historial.
     *
     * @param string $idTracking
     * @param string $baseUrl
     * @param PendingRequest $http
     *
     * @return void
     * @throws ConnectionException
     */
    private static function procesarTrackingId(string $idTracking, string $baseUrl, PendingRequest $http): void
    {
        // Detalle del paquete
        $pkgData = self::obtenerDetallePaquete($baseUrl, $http, $idTracking);
        if ($pkgData === null) {
            // Ya se registró el motivo en logs
            return;
        }

        $aerotrack        = Arr::get($pkgData, 'aerotrack');
        $graphicStationId = Arr::get($pkgData, 'graphicStationID');

        // Buscar tracking local
        $tracking = Tracking::where('IDTRACKING', $idTracking)->first();
        Log::info('Aeropost: procesando tracking.', ['payload' => $pkgData, 'graphicStationId' => $graphicStationId, 'tracking' => $tracking]);

        if (!$tracking) {
            return;
        }

        // Actualizar estados y TrackingProveedor
        self::actualizarTrackingYProveedor($tracking, $aerotrack, $graphicStationId);

        // Historial de estados
        if (!$aerotrack) {
            Log::warning('Aeropost: aerotrack vacío, no se consulta historial.', ['id' => $idTracking]);
            return;
        }

        $history = self::obtenerHistorial($baseUrl, $http, $aerotrack, $idTracking);
        if (!is_array($history)) {
            return;
        }

        self::nuevosHistoriales($tracking, $history);
    }

    /**
     * Obtiene y valida el token desde la API + cache. Loguea errores y no lanza excepción.
     *
     * @return string|null Token o null si no disponible.
     */
    private static function obtenerTokenOLog(): ?string
    {
        try {
            ServicioAeropost::ObtenerTokenAcceso();
        } catch (\Throwable $e) {
            Log::error('Aeropost: error obteniendo token', ['ex' => $e->getMessage()]);
            return null;
        }

        $token = Cache::get('aeropost_access_token');
        if (!$token) {
            Log::error('Aeropost: token no disponible en cache.');
            return null;
        }
        return $token;
    }

    /**
     * Construye el cliente HTTP con los headers y timeout requeridos.
     *
     * @param string $token Bearer token.
     * @return PendingRequest
     *
     */
    private static function buildHttpClient(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . self::ObtenerTokenAcceso(),
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ]);
    }

    /**
     * Llama a /api/v2/packages/{idTracking} y devuelve el JSON decodificado o null si no ok.
     *
     * @param string $baseUrl
     * @param PendingRequest $http
     * @param string $idTracking
     *
     * @return array<string,mixed>|null
     * @throws ConnectionException
     */
    private static function obtenerDetallePaquete(string $baseUrl, PendingRequest $http, string $idTracking): ?array
    {
        $pkgUrl = "{$baseUrl}/api/v2/packages/{$idTracking}";
        $res    = $http->retry(2, 300)->get($pkgUrl);

        Log::info('Aeropost: respuesta packages.', ['id' => $idTracking, 'status' => $res->status()]);

        if (!$res->ok()) {
            if ($res->status() >= 500) {
                Log::warning('Aeropost 5xx en packages.', ['id' => $idTracking, 'status' => $res->status()]);
            } else {
                Log::warning('Aeropost: respuesta no OK en packages.', [
                    'id' => $idTracking, 'status' => $res->status(), 'body' => $res->body()
                ]);
            }
            return null;
        }

        return $res->json() ?? [];
    }

    /**
     * Actualiza ESTADOSINCRONIZADO/ESTADOMBOX y el TrackingProveedor (aerotrack) si aplica.
     *
     * @param Tracking         $tracking
     * @param string|null      $aerotrack
     * @param int|string|null  $graphicStationId
     *
     * @return void
     */
    private static function actualizarTrackingYProveedor(Tracking $tracking, ?string $aerotrack, int|string|null $graphicStationId): void
    {
        // Estado sincronizado
        if ($graphicStationId !== null) {
            $estado = self::mapEstadoAeropost((int) $graphicStationId);
            Log::info('Aeropost: estado mapeado.', [
                'id' => $tracking->IDTRACKING,
                'graphicStationId' => $graphicStationId,
                'estado' => $estado
            ]);

            if ($estado !== null) {
                if ($tracking->ESTADOSINCRONIZADO == $tracking->ESTADOMBOX) {
                    $tracking->ESTADOMBOX = $estado;
                }
                $tracking->ESTADOSINCRONIZADO = $estado;
                $tracking->save();

                Log::info('Tracking Aeropost actualizado.', [
                    'id' => $tracking->IDTRACKING, 'estado' => $estado
                ]);
            }
        }

        // TrackingProveedor
        if ($aerotrack) {
            $trackingProveedor = TrackingProveedor::where('IDTRACKING', $tracking->id)->first();
            if ($trackingProveedor) {
                $trackingProveedor->TRACKINGPROVEEDOR = $aerotrack;
                $trackingProveedor->save();
            }
        }
    }

    /**
     * Llama a /api/v2/packages/{aerotrack}/status-history. Devuelve array o null si no ok.
     *
     * @param string          $baseUrl
     * @param PendingRequest  $http
     * @param string          $aerotrack
     * @param string          $idTracking  Para logs.
     *
     * @return array<int,array<string,mixed>>|null
     */
    private static function obtenerHistorial(string $baseUrl, PendingRequest $http, string $aerotrack, string $idTracking): ?array
    {
        $url  = "{$baseUrl}/api/v2/packages/{$aerotrack}/status-history";
        $res  = $http->retry(2, 300)->get($url);

        if (!$res->ok()) {
            Log::warning('Aeropost: respuesta no OK en status-history.', [
                'id' => $idTracking, 'status' => $res->status(), 'body' => $res->body()
            ]);
            return null;
        }

        $history = $res->json();
        if (!is_array($history)) {
            Log::warning('Aeropost: status-history no es arreglo.', ['id' => $idTracking, 'payload' => $history]);
            return null;
        }

        Log::info('Aeropost: historial obtenido.', ['id' => $idTracking, 'count' => count($history)]);
        return $history;
    }

    /**
     * Persiste entradas de historial nuevas (según la última fecha guardada).
     *
     * @param Tracking                                   $tracking
     * @param array<int,array{date?:string,status?:array{description?:string},gatewayDescription?:string}> $history
     *
     * @return void
     */
    private static function nuevosHistoriales(Tracking $tracking, array $history): void
    {
        // última fecha registrada para este tracking
        $ultima   = $tracking->fechaUltimoHistorial();
        $ultimaAt = $ultima ? Carbon::parse($ultima) : $tracking->created_at->copy()->startOfDay();

        foreach ($history as $state) {
            $fechaStr = Arr::get($state, 'date');
            $status   = Arr::get($state, 'status');
            $gateway  = Arr::get($state, 'gatewayDescription');

            Log::info('Aeropost: procesando estado.', [
                'id' => $tracking->IDTRACKING, 'date' => $fechaStr, 'status' => $status, 'gateway' => $gateway
            ]);

            if (!$fechaStr || !$status) {
                continue;
            }

            $fechaAt = self::parseFecha($fechaStr);
            Log::info('Aeropost: fecha parseada.', ['ultimaAt' => $ultimaAt, 'fechaAt' => $fechaAt]);

            if ($fechaAt && (!$ultimaAt || $fechaAt->gt($ultimaAt))) {
                $historial = new TrackingHistorial();
                $historial->DESCRIPCION            = Arr::get($status, 'description', '');
                $historial->DESCRIPCIONMODIFICADA  = '';
                $historial->PAISESTADO             = $gateway ?? '';
                $historial->CODIGOPOSTAL           = 99999;
                $historial->OCULTADO               = false;
                $historial->TIPO                   = EnumTipoHistorialTracking::AEROPOST->value;
                $historial->created_at             = $fechaAt->format('Y-m-d H:i:s');
                $historial->updated_at             = $fechaAt->format('Y-m-d H:i:s');
                $historial->IDCOURIER              = $tracking->courrierNombreAId('Aeropost');
                $historial->IDTRACKING             = $tracking->id;
                $historial->save();

                $ultimaAt = $fechaAt;
            }
        }
    }

    /**
     * Parsea una fecha de la API a Carbon, devolviendo null si falla.
     *
     * @param string $fecha
     * @return Carbon|null
     */
    private static function parseFecha(string $fecha): ?Carbon
    {
        try {
            return Carbon::parse($fecha);
        } catch (\Throwable) {
            return null;
        }
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
            3 => 'Recibido Miami',
            4 => 'Tránsito a CR',
            5, 6, 7 => 'Proceso Aduanas',
            8 => 'Oficinas MB',
            default => null,
        };
    }


    /**
     * @param Tracking $tracking
     * @param float $valor
     * @param string $descripcion
     * @param int $idProveedor
     * @return Prealerta
     * @throws ConnectionException
     * @throws ExceptionAPCourierNoObtenido
     * @throws ExceptionAPCouriersNoObtenidos
     * @throws ExceptionAPTokenNoObtenido
     * @throws ExceptionAPRequestRegistrarPrealerta
     * @throws ExceptionAeropost
     * @throws QueryException
     *
     */
    public static function RegistrarPrealerta(Tracking $tracking, float $valor, string $descripcion, int $idProveedor): Prealerta
    {
        // 1. Obtener el token de acceso
        // 2. Obtenemos los couriers de Aeropost
        // 3. Obtenemos el courier de nuestro idTracking gracias al regex
        // 4. Al obtener el courier, nombreTienda va a ser: Tienda de {courier}
        // 5. Enviar el request para crear la prealerta
        // 5.1. Si da error 500, hacer el req de solicitar paquete para ver si nos envio un falso positivo (nos dice que no se guardo cuando si, esto lo hace RequestRegistrarPrealerta)
        // 6. Crear el trackingProveedor
        // 7. Crear la prealerta

        // 1. Obtener el token de acceso
        ServicioAeropost::ObtenerTokenAcceso();

        // 2. Obtenemos los couriers de Aeropost
        $couriers = self::ObtenerCouriers();

        // 3. Obtenemos el courier de nuestro idTracking gracias al regex
        $courierSeleccionado = self::ObtenerCourier($couriers, $tracking->IDTRACKING);
        Log::info('CourierSeleccionado: '.json_encode($courierSeleccionado));

        // 4. Al obtener el courier, nombreTienda va a ser: Tienda de {courier}
        $nombreTienda = 'Tienda de '.$courierSeleccionado['name'];

        // 5. Enviar el request para crear la prealerta
        try {
            $idPrealerta = self::RequestRegistrarPrealerta(2979592, $courierSeleccionado['id'], $tracking->IDTRACKING, $nombreTienda, $valor, $descripcion);
        } catch(Exception $e) {
            // # Como a veces el API de AP de registrar prealerta falla, verificar con otro endpoint y obtener el idPrealerta
            $dataGetPackage = self::ObtenerPaquete($tracking->IDTRACKING);
            Log::info('[ServicioAeropost,RP] campo noteId: '. $dataGetPackage['noteId']);
            $idPrealerta = $dataGetPackage['noteId'];
        }
        Log::info('[ServicioAeropost,RP] idPrealerta '.$idPrealerta);

        // 6. Crear el trackingProveedor
        $trackingProveedor = new TrackingProveedor;
        $trackingProveedor->IDTRACKING = $tracking->id;
        $trackingProveedor->IDPROVEEDOR = $idProveedor;
        $trackingProveedor->save();

        // 7. Crear la prealerta
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
     * @return string
     * @throws ConnectionException
     * @throws ExceptionAPTokenNoObtenido
     */
    private static function ObtenerTokenAcceso(): string
    {
        // 1. Obtener el token de acceso
        // 1.1 Si se tiene, solicitarlo desde la caché
        // 1.2 Si no, se solicita mediante el endpoint
        // 2. Se guarda en la caché

        $intentos = 0;

        // 1.1 Si se tiene, solicitarlo desde la caché
        if (Cache::has('aeropost_access_token')) {
            return Cache::get('aeropost_access_token');
        }

        // 1.2 Si no, se solicita mediante el endpoint
        $urlAccesoToken = env('AEROPOST_URL_AUTH');

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic '.base64_encode(env('AEROPOST_CLIENT_ID').':'.env('AEROPOST_CLIENT_SECRET')),
        ];

        // - Si hay error, intentarlo 2 veces mas
        $accessTokenRespuesta = null;
        while ($intentos < 3) {
            $respuesta = Http::withHeaders($headers)
                ->asForm() // Esto fuerza a que se envie como x-www-urlenconded
                ->post($urlAccesoToken, [
                    'grant_type' => env('AEROPOST_GRANTTYPE'),
                    'scope' => env('AEROPOST_SCOPE'),
                    'username' => env('AEROPOST_USERNAME'),
                    'password' => env('AEROPOST_PASSWORD'),
                    'gateway' => env('AEROPOST_GATEWAY'),
                ]);

            if (! $respuesta->successful()) {
                $intentos++;
            } else {
                $accessTokenRespuesta = $respuesta;
                $intentos = 3;
            }
        }

        if (! $accessTokenRespuesta) {
            Log::info('[ServicioAP->ObtenerTokenAcceso] No se recibio el token de acceso');
            throw new ExceptionAPTokenNoObtenido;
        }

        if (! $accessTokenRespuesta['access_token'] | ! $accessTokenRespuesta['refresh_token']) {
            Log::info('[ServicioAP->ObtenerTokenAcceso] El request fue exitoso pero no esta el campo de access_token y/o refresh_token');
            throw new ExceptionAPTokenNoObtenido('El request fue exitoso pero no esta el campo de access_token y/o refresh_token');
        }

        Cache::put('aeropost_access_token', $accessTokenRespuesta['access_token'], $accessTokenRespuesta['expires_in'] - 60);

        return $accessTokenRespuesta['access_token'];

    }

    /**
     * @return array que es el reponseAPI de todos los couriers de AP
     * @throws ConnectionException
     * @throws ExceptionAPCouriersNoObtenidos
     */
    public static function ObtenerCouriers(): array
    {
        // 1. Verificar si los couriers estan en la cache
        // 2. Si no estan entonces hacer la llamada a AP API Couriers
        // 3. Si hay un error, hacer 3 intentos mas
        // 4. Guardarlo en la cache por 1 dia

        $intentos = 0;

        // 1. Verificar si los couriers estan en la cache
        if (Cache::has('aeropost_couriers')) {
            return Cache::get('aeropost_couriers');
        }

        // 2. Si no estan entonces hacer la llamada a AP API Couriers
        $urlCourier = env('AEROPOST_URL_BASE').'/api/couriers';
        $headers = [
            'Authorization' => 'Bearer '.Cache::get('aeropost_access_token'),
        ];

        // 3. Si hay un error, hacer 3 intentos mas
        $couriers = [];
        while ($intentos < 3) {

            $respuesta = Http::withHeaders($headers)->get($urlCourier, []);

            // cualquier status que no es 2xx
            if (! $respuesta->successful()) {
                $intentos++;
            } else {
                $couriers = $respuesta->json();
                $intentos = 3;
            }

        }

        if (count($couriers) == 0) {
            Log::info('[ServicioAeropost,OC] error: Hubo un error en el request de couriers. No se trajo los couriers.');
            throw new ExceptionAPCouriersNoObtenidos('Hubo un error en el request de couriers. No se trajo los couriers.');
        }
        // 4. Guardarlo en la cache por 1 dia
        Cache::put('aeropost_couriers', $couriers, now()->addDays(1));

        return $couriers;
    }

    /**
     * @param array $couriers
     * @param string $idTracking
     * @return array
     * @throws ExceptionAPCourierNoObtenido
     */
    public static function ObtenerCourier(array $couriers, string $idTracking): array
    {
        // 1. Recorremos los couriers
        // 2. Obtenemos los regex de cada courier y los comparamos con el idTracking
        // 3. Si cumple con alguno, se retorna t#do el array asociativo
        // 4. Si no cumple con ninguno, obtener el courier que es Other (en los docs el default es el courierId 0)
        $courierSeleccionado = null;


        // 1. Recorremos los couriers
        foreach ($couriers as $courier) {
            // 2. Obtenemos los regex de cada courier y los comparamos con el idTracking
            $regexActual = $courier['regex'];

            // - Se excluye el courier de id 0 => Other/Default que no tiene el campo de regex
            if ($courier['id'] == 0 || $courier['id'] == 9) {
                continue;
            }

            if (! $regexActual) {
                throw new ExceptionAPCourierNoObtenido('El elemento courier no contiene el campo regex');
            }

            // 3. Si con alguno, se retorna t#do el array asociativo
            // - Antes de hacer el preg_match necesito agregarle a ambos lados
            $regexDepurado = ActionDepurarRegex::execute($regexActual);

            if (preg_match($regexDepurado, $idTracking)) {
                $courierSeleccionado = $courier;
                break;
            }
        }

        // 4. Si no cumple con ninguno, obtener el courier que es Other/Default (en los docs el default es el courierId 0)
        if (! $courierSeleccionado) {
            $courierDefault = null;

            foreach ($couriers as $courier) {
                $courierId = $courier['id'];

                if (! isset($courierId)) { //isset porque si solo pongo !$courierId 0 me lo toma como false y entra cuando no deberia
                    Log::info('[ServicioAeropost,OC] El campo id en un elemento de courier no existe'. json_encode($couriers));
                    throw new ExceptionAPCourierNoObtenido('El campo id en un elemento de courier no existe');
                }
                if ($courierId == 0 || $courier['id'] == 9) {
                    $courierDefault = $courier;
                    break;
                }
            }

            if (! $courierDefault) {
                Log::info('[ServicioAeropost,OC] No se encontro el courier default');
                throw new ExceptionAPCourierNoObtenido('No se encontro el courier default');
            }
            $courierSeleccionado = $courierDefault;
        }

        return $courierSeleccionado;
    }

    /**
     * @param int $consigneeId
     * @param int $courierId
     * @param string $numeroTracking
     * @param string $nombreTienda
     * @param float $valor
     * @param string $descripcion
     * @return int
     * @throws ExceptionAPRequestRegistrarPrealerta
     */
    public static function RequestRegistrarPrealerta(int $consigneeId, int $courierId, string $numeroTracking, string $nombreTienda, float $valor, string $descripcion): int
    {
        // 1. Crear los encabezados
        // 2. Poner los datos necesarios
        // 3. Si algo falla, lanzar excepcion
        // 3.1 Si es error 500, entonces verificar con el GETPACKAGES si hay uno con el numeroTracking
        // 4. Verificar que todos los campos que necesito se encuentren (id principalmente)

        $url = env('AEROPOST_URL_BASE').'/api/pre-alerts?language=en';
        // 1. Crear los encabezados
        $headers = [
            'Authorization' => 'Bearer '.Cache::get('aeropost_access_token'),
            'Accept' => 'application/json',
            'content-type' => 'application/json',
        ];

        // - Se lanza el try para atrapar el ConnectionException (Si no se logro hacer conexión con el servidor de AP del t#do, duró mucho el request, etc) para envolverlo en la excepcion ExceptionAPRequestRegistrarPrealerta
        try {
            // 2. Poner los datos necesarios
            $respuesta = Http::withHeaders($headers)->post($url, [
                'consigneeId' => $consigneeId,
                'courierId' => $courierId,
                'courierTracking' => $numeroTracking,
                'storeName' => $nombreTienda,
                'value' => $valor,
                'description' => $descripcion,
            ]);
            // investigar que es ConnectionException
            Log::info('[ServicioAeropost,RRP] RequestRegistrarPrealerta: '.json_encode($respuesta->json()));
        } catch (ConnectionException $e) {
            throw new ExceptionAPRequestRegistrarPrealerta('No se consiguió respuesta alguna del servidor de Aeropost');
        }
        // 3. Si algo falla (porque el servidor de AP nos lo envía, ya sea error o no), lanzar excepcion
        if (! $respuesta->successful()) {
            // falta log
            throw new ExceptionAPRequestRegistrarPrealerta;
        }

        // 4. Verificar que todos los campos que necesito se encuentren (id principalmente)
        if (! $respuesta['id']) {
            throw new ExceptionAPRequestRegistrarPrealerta(' El request se dio con exito pero no trae el campo id');
        }

        return $respuesta['id'];
    }

    /**
     * @throws ExceptionAPTokenNoObtenido
     * @throws ConnectionException
     * @throws ExceptionAPRequestActualizarPrealerta
     */
    public static function ActualizarPrealerta(int $idPrealerta, string $descripcion, float $valor, string $numeroTracking, string $nombreTienda, int $courierId, int $consigneeId): void{
        // 1. Obtener el token de Acceso
        // 2. Actualizar los campos

        $url = env('AEROPOST_URL_BASE').'/api/pre-alerts/' . $idPrealerta . '?language=en';
        // 1. Crear los encabezados
        $headers = [
            'Authorization' => 'Bearer '. self::ObtenerTokenAcceso(),
            'Accept' => 'application/json',
            'content-type' => 'application/json',
        ];

        // - Se lanza el try para atrapar el ConnectionException (Si no se logro hacer conexión con el servidor de AP del t#do, duró mucho el request, etc) para envolverlo en la excepcion ExceptionAPRequestRegistrarPrealerta
        try {
            // Poner los datos necesarios
            $respuesta = Http::withHeaders($headers)->put($url, [
                'consigneeId' => $consigneeId,
                'courierId' => $courierId,
                'courierTracking' => $numeroTracking,
                'storeName' => $nombreTienda,
                'value' => $valor,
                'description' => $descripcion,
            ]);
            // investigar que es ConnectionException
            Log::info('[ServicioAeropost, AP] ActualizarPrealerta: '.json_encode($respuesta->json()));
        } catch (ConnectionException $e) {
            throw new ExceptionAPRequestActualizarPrealerta('No se consiguió respuesta alguna del servidor de Aeropost');
        }

        // Si algo falla (porque el servidor de AP nos lo envía, ya sea error o no), lanzar excepcion
        if (!$respuesta->successful()) {
            // falta log
            throw new ExceptionAPRequestActualizarPrealerta('La respuesta de AP no fue exitosa');
        }

        Log::info('[ServicioAeropost, AP] Prealerta de AP con exito! ');

    }

    /**
     * @param int $idPrealerta
     * @return void
     * @throws ConnectionException
     * @throws ExceptionAPRequestEliminarPrealerta
     * @throws ExceptionAPTokenNoObtenido
     */
    public static function EliminarPrealerta(int $idPrealerta): void {
        // 1. Con el $idPrealerta elimino la prealerta de aeropost

        $url = env('AEROPOST_URL_BASE').'/api/pre-alerts/' . $idPrealerta . '?language=en';
        // 1. Crear los encabezados
        $headers = [
            'Authorization' => 'Bearer '. self::ObtenerTokenAcceso(),
            'Accept' => 'application/json',
            'content-type' => 'application/json',
        ];

        // - Se lanza el try para atrapar el ConnectionException (Si no se logro hacer conexión con el servidor de AP del t#do, duró mucho el request, etc) para envolverlo en la excepcion ExceptionAPRequestRegistrarPrealerta
        try {
            // Poner los datos necesarios
            $respuesta = Http::withHeaders($headers)->delete($url, []);
            // investigar que es ConnectionException
            Log::info('[ServicioAeropost, EP] EliminarPrealerta: '.json_encode($respuesta->json()));
        } catch (ConnectionException $e) {
            throw new ExceptionAPRequestEliminarPrealerta('No se consiguió respuesta alguna del servidor de Aeropost');
        }

    }

    /**
     * @param string $numeroTracking
     * @return array
     * @throws ConnectionException
     * @throws ExceptionAPTokenNoObtenido
     * @throws ExceptionAeropost
     */
    public static function ObtenerPaquete(string $numeroTracking): array{
        // 1. Llama al API de GetPackage
        // 2. Retorna el contenido
        // # Proposito: Si se necesita toda la data llamar a esta funcion de integracion
        $url = env('AEROPOST_URL_BASE').'/api/v2/packages/' . $numeroTracking . '?language=en';
        // 1. Crear los encabezados
        $headers = [
            'Authorization' => 'Bearer '. self::ObtenerTokenAcceso(),
            'Accept' => 'application/json',
            'content-type' => 'application/json',
        ];

        // - Se lanza el try para atrapar el ConnectionException (Si no se logro hacer conexión con el servidor de AP del t#do, duró mucho el request, etc) para envolverlo en la excepcion ExceptionAPRequestRegistrarPrealerta
        try {
            // Poner los datos necesarios
            $respuesta = Http::withHeaders($headers)->get($url);
            // investigar que es ConnectionException
            Log::info('[ServicioAeropost, OP] ObtenerPaquete: '.json_encode($respuesta->json()));
        } catch (ConnectionException $e) {
            throw new ExceptionAeropost('No se consiguió respuesta alguna del servidor de Aeropost');
        }

        // Si algo falla (porque el servidor de AP nos lo envía, ya sea error o no), lanzar excepcion
        if (!$respuesta->successful()) {
            // falta log
            throw new ExceptionAeropost('La respuesta de AP no fue exitosa');
        }

        return $respuesta->json();
    }
}
