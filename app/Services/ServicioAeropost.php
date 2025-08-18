<?php

namespace App\Services;

use App\Actions\ActionDepurarRegex;
use App\Exceptions\ExceptionAPCourierNoObtenido;
use App\Exceptions\ExceptionAPCouriersNoObtenidos;
use App\Exceptions\ExceptionAPRequestRegistrarPrealerta;
use App\Exceptions\ExceptionAPTokenNoObtenido;
use App\Models\Prealerta;
use App\Models\Tracking;
use App\Models\TrackingProveedor;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ServicioAeropost
{
    /**
     * @throws ConnectionException
     * @throws ExceptionAPCouriersNoObtenidos
     * @throws ExceptionAPTokenNoObtenido
     * @throws ExceptionAPCourierNoObtenido
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
        $tokenAcceso = ServicioAeropost::ObtenerTokenAcceso();

        // 2. Obtenemos los couriers de Aeropost
        $couriers = ServicioAeropost::ObtenerCouriers();
        //Log::info('Couriers desde AEROPOST: ' . json_encode($couriers));

        // 3. Obtenemos el courier de nuestro idTracking gracias al regex
        $courierSeleccionado = ServicioAeropost::ObtenerCourier($couriers, $tracking->IDTRACKING);
        Log::info('CourierSeleccionado: ' . json_encode($courierSeleccionado));

        // 4. Al obtener el courier, nombreTienda va a ser: Tienda de {courier}
        $nombreTienda = 'Tienda de ' . $courierSeleccionado['name'];

        // 5. Enviar el request para crear la prealerta
        $idPrealerta = self::RequestRegistrarPrealerta(2979592, $courierSeleccionado['id'], $tracking->IDTRACKING, $nombreTienda, $valor, $descripcion);
        Log::info('[ServicioAeropost,RP] idPrealerta ' . $idPrealerta);

        // 6. Crear el trackingProveedor
        $trackingProveedor = new TrackingProveedor();
        $trackingProveedor->IDTRACKING = $tracking->id;
        $trackingProveedor->IDPROVEEDOR = $idProveedor;
        $trackingProveedor->save();

        // 7. Crear la prealerta
        $prealerta = new Prealerta();
        $prealerta->DESCRIPCION = $descripcion;
        $prealerta->VALOR = $valor;
        $prealerta->NOMBRETIENDA =  $nombreTienda;
        $prealerta->IDCOURIER = $courierSeleccionado['id'];
        $prealerta->IDPREALERTA =  $idPrealerta;
        $prealerta->IDTRACKINGPROVEEDOR = $trackingProveedor->id;
        $prealerta->save();

        return $prealerta;
    }

    /**
     * @throws ExceptionAPTokenNoObtenido
     * @throws ConnectionException
     */
    public static function ObtenerTokenAcceso(): string
    {
        // 1. Obtener el token de acceso
        // 1.1 Si se tiene, solicitarlo desde la caché
        // 1.2 Si no, se solicita mediante el endpoint
        // 2. Se guarda en la caché

        $intentos = 0;

        // 1.1 Si se tiene, solicitarlo desde la caché
        if (Cache::has('aeropost_access_token')) {
            $accessToken = Cache::get('aeropost_access_token');
        }

        // 1.2 Si no, se solicita mediante el endpoint
        $urlAccesoToken = env('AEROPOST_URL_AUTH');

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode(env('AEROPOST_CLIENT_ID') . ':' . env('AEROPOST_CLIENT_SECRET')),
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

            if (!$respuesta->successful()) {
                $intentos++;
            } else {
                $accessTokenRespuesta = $respuesta;
                $intentos = 3;
            }
        }

        if (!$accessTokenRespuesta) {
            Log::info('[ServicioAP->ObtenerTokenAcceso] No se recibio el token de acceso');
            throw new ExceptionAPTokenNoObtenido();
        }


        if (!$accessTokenRespuesta['access_token'] | !$accessTokenRespuesta['refresh_token']) {
            Log::info('[ServicioAP->ObtenerTokenAcceso] El request fue exitoso pero no esta el campo de access_token y/o refresh_token');
            throw new ExceptionAPTokenNoObtenido('El request fue exitoso pero no esta el campo de access_token y/o refresh_token');
        }

        Cache::put('aeropost_access_token', $accessTokenRespuesta['access_token'], $accessTokenRespuesta['expires_in'] - 60);
        return $accessTokenRespuesta['access_token'];

    }

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
        $urlCourier = env('AEROPOST_URL_BASE') . '/api/couriers';
        $headers = [
            'Authorization' => 'Bearer ' . Cache::get('aeropost_access_token'),
        ];

        // 3. Si hay un error, hacer 3 intentos mas
        $couriers = [];
        while ($intentos < 3) {

            $respuesta = Http::withHeaders($headers)->get($urlCourier, []);

            //cualquier status que no es 2xx
            if (!$respuesta->successful()) {
                $intentos++;
            } else {
                $couriers = $respuesta->json();
                $intentos = 3;
            }

        }

        if (sizeof($couriers) == 0) {
            Log::info('[ServicioAeropost,OC] error: Hubo un error en el request de couriers. No se trajo los couriers.');
            throw new ExceptionAPCouriersNoObtenidos("Hubo un error en el request de couriers. No se trajo los couriers.");
        }
        // 4. Guardarlo en la cache por 1 dia
        Cache::put('aeropost_couriers', $couriers, now()->addDays(1));

        return $couriers;
    }

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

            if (!$regexActual) {
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
        if (!$courierSeleccionado) {
            $courierDefault = null;

            foreach ($couriers as $courier) {
                $courierId = $courier['id'];

                if (!$courierId) {
                    Log::info('[ServicioAeropost,OC] El campo id en un elemento de courier no existe');
                    throw new ExceptionAPCourierNoObtenido('El campo id en un elemento de courier no existe');
                }
                if ($courierId == 0) {
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
     * @throws ConnectionException
     */
    public static function RequestRegistrarPrealerta(int $consigneeId, int $courierId, string $numeroTracking, string $nombreTienda, float $valor, string $descripcion): int
    {
        // 1. Crear los encabezados
        // 2. Poner los datos necesarios
        // 3. Si algo falla, lanzar excepcion
        // 3.1 Si es error 500, entonces verificar con el GETPACKAGES si hay uno con el numeroTracking
        // 4. Verificar que todos los campos que necesito se encuentren (id principalmente)

        $url = env('AEROPOST_URL_BASE') . '/api/pre-alerts?language=en';
        // 1. Crear los encabezados
        $headers = [
            'Authorization' => 'Bearer ' . Cache::get('aeropost_access_token'),
            'Accept' => 'application/json',
            'content-type' => 'application/json',
        ];

        // 2. Poner los datos necesarios
        $respuesta = Http::withHeaders($headers)->post($url, [
            "consigneeId" => $consigneeId,
            "courierId" => $courierId,
            "courierTracking" => $numeroTracking,
            "storeName" => $nombreTienda,
            "value" => $valor,
            "description" => $descripcion
        ]);
        // investigar que es ConnectionException
        Log::info('[ServicioAeropost,RRP] RequestRegistrarPrealerta: ' . json_encode($respuesta->json()));

        // 3. Si algo falla, lanzar excepcion
        if (!$respuesta->successful()) {
            //falta log
            throw new ExceptionAPRequestRegistrarPrealerta();
        }

        // 4. Verificar que todos los campos que necesito se encuentren (id principalmente)
        if (!$respuesta['id']) {
            throw new ExceptionAPRequestRegistrarPrealerta(' El request se dio con exito pero no trae el campo id');
        }

        return $respuesta['id'];
    }
}
