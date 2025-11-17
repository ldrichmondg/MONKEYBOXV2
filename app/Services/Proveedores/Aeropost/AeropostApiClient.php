<?php

namespace App\Services\Proveedores\Aeropost;

use App\Exceptions\ExceptionAeropost;
use App\Exceptions\ExceptionAPCouriersNoObtenidos;
use App\Exceptions\ExceptionAPObtenerPaquetes;
use App\Exceptions\ExceptionAPRequestActualizarPrealerta;
use App\Exceptions\ExceptionAPRequestEliminarPrealerta;
use App\Exceptions\ExceptionAPRequestRegistrarPrealerta;
use App\Exceptions\ExceptionAPTokenNoObtenido;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AeropostApiClient
{
    private string $token;
    private string $baseUrl;
    private PendingRequest $http;

    /**
     * @throws ExceptionAPTokenNoObtenido
     * @throws ConnectionException
     */
    public function __construct(bool $fake = false)
    {
        // es para probar el ambito de prueba de Aeropost
        if ($fake){
            $this->baseUrl = config('services.aeropostDev.url_base');
        }else{
            $this->baseUrl = config('services.aeropost.url_base');
        }

        $this->token = $this->obtenerTokenAcceso($fake);
        $this->http = $this->buildHttpClient(); //primero llamar a token antes de esta funcion sino no funciona
    }


    /**
     * Construye el cliente HTTP con los headers y timeout requeridos.
     *
     * @return PendingRequest
     */
    private function buildHttpClient(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * @return string
     * @throws ConnectionException
     * @throws ExceptionAPTokenNoObtenido
     */
    private function ObtenerTokenAcceso(bool $fake = false): string
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

        /*Log::info('[AeroPost Config]', [
            'grant_type' => config('services.aeropost.grant_type'),
            'scope' => config('services.aeropost.scope'),
            'username' => config('services.aeropost.username'),
            'password' => config('services.aeropost.password'),
            'gateway' => config('services.aeropost.gateway'),
            'url_auth' => config('services.aeropost.url_auth'),
            'url_base' => config('services.aeropost.url_base'),
            'client_id' => config('services.aeropost.client_id'),
            'client_secret' => config('services.aeropost.client_secret'),
        ]);*/

        // 1.2 Si no, se solicita mediante el endpoint
        if ($fake){
            $urlAccesoToken = config('services.aeropostDev.url_auth');
            $grantType = config('services.aeropostDev.grant_type');
            $scope = config('services.aeropostDev.scope');
            $username =  config('services.aeropostDev.username');
            $password = config('services.aeropostDev.password');
            $gateway =  config('services.aeropostDev.gateway');
        }else{
            $urlAccesoToken = config('services.aeropost.url_auth');
            $grantType = config('services.aeropost.grant_type');
            $scope = config('services.aeropost.scope');
            $username =  config('services.aeropost.username');
            $password = config('services.aeropost.password');
            $gateway =  config('services.aeropost.gateway');
        }

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode(config('services.aeropost.client_id') . ':' . config('services.aeropost.client_secret')),
        ];

        // - Si hay error, intentarlo 2 veces mas
        $accessTokenRespuesta = null;
        while ($intentos < 3) {
            $respuesta = Http::withHeaders($headers)
                ->asForm() // Esto fuerza a que se envie como x-www-urlenconded
                ->post($urlAccesoToken, [
                    'grant_type' => $grantType,
                    'scope' => $scope,
                    'username' => $username,
                    'password' => $password,
                    'gateway' => $gateway,
                ]);

            if (!$respuesta->successful()) {
                $intentos++;
            } else {
                $accessTokenRespuesta = $respuesta;
                $intentos = 3;
            }
        }

        if (!$accessTokenRespuesta) {
            Log::info('[ServicioAP->ObtenerTokenAcceso] No se recibio el token de acceso');
            throw new ExceptionAPTokenNoObtenido;
        }

        if (!$accessTokenRespuesta['access_token'] | !$accessTokenRespuesta['refresh_token']) {
            Log::info('[ServicioAP->ObtenerTokenAcceso] El request fue exitoso pero no esta el campo de access_token y/o refresh_token');
            throw new ExceptionAPTokenNoObtenido('El request fue exitoso pero no esta el campo de access_token y/o refresh_token');
        }

        //Log::info('[OTA] Token: ' . $accessTokenRespuesta['access_token']);
        Cache::put('aeropost_access_token', $accessTokenRespuesta['access_token'], $accessTokenRespuesta['expires_in'] - 70);

        return $accessTokenRespuesta['access_token'];

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
    public function RegistrarPrealerta(int $consigneeId, int $courierId, string $numeroTracking, string $nombreTienda, float $valor, string $descripcion): int
    {
        // 1. Crear los encabezados
        // 2. Poner los datos necesarios
        // 3. Si algo falla, lanzar excepcion
        // 3.1 Si es error 500, entonces verificar con el GETPACKAGES si hay uno con el numeroTracking
        // 4. Verificar que todos los campos que necesito se encuentren (id principalmente)

        $url = $this->baseUrl . '/api/pre-alerts?language=en';

        // - Se lanza el try para atrapar el ConnectionException (Si no se logro hacer conexión con el servidor de AP del t#do, duró mucho el request, etc) para envolverlo en la excepcion ExceptionAPRequestRegistrarPrealerta
        try {
            // 2. Poner los datos necesarios
            $respuesta = $this->http->post($url, [
                'consigneeId' => $consigneeId,
                'courierId' => $courierId,
                'courierTracking' => $numeroTracking,
                'storeName' => $nombreTienda,
                'value' => $valor,
                'description' => $descripcion,
            ]);
            // investigar que es ConnectionException
            Log::info('[ServicioAeropost,RRP] RequestRegistrarPrealerta: ' . json_encode($respuesta->json()));
        } catch (ConnectionException $e) {
            throw new ExceptionAPRequestRegistrarPrealerta('No se consiguió respuesta alguna del servidor de Aeropost');
        }
        // 3. Si algo falla (porque el servidor de AP nos lo envía, ya sea error o no), lanzar excepcion
        if (!$respuesta->successful()) {
            // falta log
            throw new ExceptionAPRequestRegistrarPrealerta;
        }

        // 4. Verificar que todos los campos que necesito se encuentren (id principalmente)
        if (!$respuesta['id']) {
            throw new ExceptionAPRequestRegistrarPrealerta(' El request se dio con exito pero no trae el campo id');
        }

        return $respuesta['id'];
    }

    /**
     * @throws ExceptionAPTokenNoObtenido
     * @throws ConnectionException
     * @throws ExceptionAPRequestActualizarPrealerta
     */
    public function ActualizarPrealerta(int $idPrealerta, string $descripcion, float $valor, string $numeroTracking, string $nombreTienda, int $courierId, int $consigneeId): void
    {
        // 2. Actualizar los campos
        $url = $this->baseUrl . '/api/pre-alerts/' . $idPrealerta . '?language=en';

        // - Se lanza el try para atrapar el ConnectionException (Si no se logro hacer conexión con el servidor de AP del t#do, duró mucho el request, etc) para envolverlo en la excepcion ExceptionAPRequestRegistrarPrealerta
        try {
            // Poner los datos necesarios
            $respuesta = $this->http->put($url, [
                'consigneeId' => $consigneeId,
                'courierId' => $courierId,
                'courierTracking' => $numeroTracking,
                'storeName' => $nombreTienda,
                'value' => $valor,
                'description' => $descripcion,
            ]);

            Log::info('[ServicioAeropost, AP] ActualizarPrealerta: ' . json_encode($respuesta->json()));
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
    public function EliminarPrealerta(int $idPrealerta): void
    {
        // 1. Con el $idPrealerta elimino la prealerta de aeropost

        $url = $this->baseUrl . '/api/pre-alerts/' . $idPrealerta . '?language=en';

        // - Se lanza el try para atrapar el ConnectionException (Si no se logro hacer conexión con el servidor de AP del t#do, duró mucho el request, etc) para envolverlo en la excepcion ExceptionAPRequestRegistrarPrealerta
        try {
            // Poner los datos necesarios
            $respuesta = $this->http->delete($url, []);
            // investigar que es ConnectionException
            Log::info('[ServicioAeropost, EP] EliminarPrealerta: ' . json_encode($respuesta->json()));
        } catch (ConnectionException $e) {
            throw new ExceptionAPRequestEliminarPrealerta('No se consiguió respuesta alguna del servidor de Aeropost');
        }

    }


    /**
     * @return array que es el reponseAPI de todos los couriers de AP
     * @throws ConnectionException
     * @throws ExceptionAPCouriersNoObtenidos
     * @throws ExceptionAPTokenNoObtenido
     */
    public function ObtenerCouriers(): array
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
        $urlCourier = $this->baseUrl . '/api/couriers';

        // 3. Si hay un error, hacer 3 intentos mas
        $couriers = [];
        while ($intentos < 3) {

            $respuesta = $this->http->get($urlCourier, []);

            // cualquier status que no es 2xx
            if (!$respuesta->successful()) {
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
     * Llama a /api/v2/packages/{idTracking} y devuelve el JSON decodificado.
     *
     * @param string $idTracking
     *
     * @return array<string,mixed>
     * @throws ExceptionAPObtenerPaquetes
     */
    public function obtenerDetallePaquete(string $idTracking): array
    {
        //Si el paquete no existem retorna un error 404 Not Found
        $pkgUrl = "{$this->baseUrl}/api/v2/packages/{$idTracking}";
        try {
            $res = $this->http->retry(2, 300)->get($pkgUrl);
        } catch (ConnectionException $e) {
            throw new ExceptionAPObtenerPaquetes($idTracking, null, "Error de conexión: {$e->getMessage()}");
        }

        //Si la respuesta fue procesada
        //Log::info('Aeropost: respuesta packages.', ['id' => $idTracking, 'status' => $res->status()]);

        if (!$res->ok()) {
            throw new ExceptionAPObtenerPaquetes($idTracking, $res->status(), $res->body());
        }

        return $res->json();
    }


    /**
     * @param string $numeroTracking
     * @return array
     * @throws ExceptionAPTokenNoObtenido
     * @throws ExceptionAeropost
     */
    public function ObtenerPaquete(string $numeroTracking): array
    {
        // 1. Llama al API de GetPackage
        // 2. Retorna el contenido
        // # Proposito: Si se necesita toda la data llamar a esta funcion de integracion
        $url = "{$this->baseUrl}/api/v2/packages/' . $numeroTracking . '?language=en";

        // - Se lanza el try para atrapar el ConnectionException (Si no se logro hacer conexión con el servidor de AP del t#do, duró mucho el request, etc) para envolverlo en la excepcion ExceptionAPRequestRegistrarPrealerta
        try {
            // Poner los datos necesarios
            $respuesta = $this->http->get($url);
            // investigar que es ConnectionException
            Log::info('[ServicioAeropost, OP] ObtenerPaquete: ' . json_encode($respuesta->json()));
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

    /**
     * @param array $trackings
     * @param int $pageSize
     * @param int $pageIndex
     * @param bool $soloAyer
     * @return string
     */
    private function CrearUrlObtenerPaquetes(
        array $trackings,
        int $pageSize,
        int $pageIndex,
        bool $soloAyer
    ): string {
        // - crear Url dependiendo de los parametros para obtener paquetes
        $baseUrl = rtrim((string)$this->baseUrl, '/');

        // Si vienen trackings → usar searchText y NO usar greaterThan
        if (count($trackings) != 0) {
            $trackingsMensajes = implode(',', $trackings);
            return "{$baseUrl}/api/v2/packages?trackingsSearchText={$trackingsMensajes}";
        }

        // Consulta general sin trackings
        $url = "{$baseUrl}/api/v2/packages?pageSize={$pageSize}&pageIndex={$pageIndex}";

        if ($soloAyer) {
            $fechaAyer = now()->subDay()->format('Y-m-d');
            $url .= "&lastUpdate=greaterThan~{$fechaAyer} 00:00:00";
        }

        return $url;
    }


    /**
     * @param array $trackings
     * @param bool $soloAyer
     * @return array
     * @throws ExceptionAPObtenerPaquetes
     */
    public function ObtenerPaquetesMasivos(array $trackings, bool $soloAyer): array{
        //obtener del API de Aeropost el estado de cada tracking consultado para una consulta masiva
        // Si trae trackings, se usa el parametro trackingsSearchText, sino se traen todos
        // 1. Obtener el total items del primer request
        // 2. Poner el page size al max (150)
        // 3. tener la cantidad de pages: totalItems/pageSize
        // 4. llamar a los demas pages que hagan falta

        $pageSize = 150;
        $pageIndex = 0;

        // Primera llamada
        $url = $this->CrearUrlObtenerPaquetes($trackings, $pageSize, $pageIndex, $soloAyer);
        $trackingsMensajes = implode(',', $trackings);
        Log::info('[OPM] Url: ' . $url);

        try {
            $respuesta = $this->http->retry(2, 300)->get($url);
        } catch (ConnectionException $e) {
            throw new ExceptionAPObtenerPaquetes($trackingsMensajes, null, "Error de conexión: {$e->getMessage()}");
        }

        // Si algo falla (porque el servidor de AP nos lo envía, ya sea error o no), lanzar excepcion
        if (!$respuesta->successful()) {
            // falta log
            throw new ExceptionAPObtenerPaquetes($trackingsMensajes, $respuesta->status(), $respuesta->body(),'La respuesta de AP no fue exitosa para la consulta masiva.');
        }

        $respuestaDatos = $respuesta->json();
        $paquetes = $respuestaDatos['packages'];
        $totalItems = $respuestaDatos['totalItems'];
        $cantidadPages = ceil($totalItems / $pageSize);

        // 4. Si la cant. de pages = 1, entonces retorna el resultado
        if($cantidadPages == 1){
            return $paquetes;
        }

        // 4. llamar a los demás pages que hagan falta
        for($pageIndex = 1; $pageIndex <= $cantidadPages; $pageIndex++){
            Log::info('TRAYENDO DEL PAGEINDEX #'. $pageIndex . " del total de pages: #".$cantidadPages);
            $url = $this->CrearUrlObtenerPaquetes($trackings, $pageSize, $pageIndex, $soloAyer);
            $paquetesPagina = $this->ObtenerPaquetesMasivosAux($url);

            // Agregarlos al array principal
            $paquetes = array_merge($paquetes, $paquetesPagina);
        }

        return $paquetes;
    }

    /**
     * @param string $url
     * @return array
     * @throws ExceptionAPObtenerPaquetes
     */
    private function ObtenerPaquetesMasivosAux(string $url): array{

        try {
            $respuesta = $this->http->retry(2, 300)->get($url);
        } catch (ConnectionException $e) {
            throw new ExceptionAPObtenerPaquetes('Busqueda Trackings', null, "Error de conexión: {$e->getMessage()}");
        }

        // Si algo falla (porque el servidor de AP nos lo envía, ya sea error o no), lanzar excepcion
        if (!$respuesta->successful()) {
            // falta log
            throw new ExceptionAPObtenerPaquetes('Busqueda Trackings', $respuesta->status(), $respuesta->body(),'La respuesta de AP no fue exitosa para la consulta masiva.');
        }

        return $respuesta->json()['packages'];
    }

    /**
     * Llama a /api/v2/packages/{aerotrack}/status-history. Devuelve array o null si no ok.
     *
     * @param string $aerotrack
     * @param string $idTracking Para logs.
     *
     * @return array<int,array<string,mixed>>|null
     * @throws ExceptionAPObtenerPaquetes
     */
    public function obtenerHistorial(string $aerotrack, string $idTracking): ?array
    {
        $url = "{$this->baseUrl}/api/v2/packages/{$aerotrack}/status-history?language=es";
        try {
            $res = $this->http->retry(2, 300)->get($url);
        } catch (ConnectionException $e) {
            throw new ExceptionAPObtenerPaquetes($idTracking, null, "Error de conexión: {$e->getMessage()}");
        }

        if (!$res->ok()) {
            throw new ExceptionAPObtenerPaquetes($idTracking, $res->status(), $res->body(), 'El request de obtener historiales dio un error.');
        }

        $history = $res->json();
        if (!is_array($history)) {
            throw new ExceptionAPObtenerPaquetes($idTracking, $res->status(), $res->body(), 'El request de obtener historiales no dio error, pero viene vacio.');
        }

        Log::info('[SA, OH] historiales obtenidos.', ['id' => $idTracking, 'count' => count($history)]);
        return $history;
    }

}
