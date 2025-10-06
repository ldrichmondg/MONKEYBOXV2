<?php

namespace App\Services;

use App\Events\EventoClienteEnlazadoPaquete;
use App\Exceptions\ExceptionAPCourierNoObtenido;
use App\Exceptions\ExceptionAPCouriersNoObtenidos;
use App\Exceptions\ExceptionAPRequestActualizarPrealerta;
use App\Exceptions\ExceptionAPRequestEliminarPrealerta;
use App\Exceptions\ExceptionAPRequestRegistrarPrealerta;
use App\Exceptions\ExceptionAPTokenNoObtenido;
use App\Exceptions\ExceptionArchivosDO;
use App\Exceptions\ExceptionEstadoNotFound;
use App\Exceptions\ExceptionPrealertaNotFound;
use App\Exceptions\ExceptionTrackingProveedorNotFound;
use App\Http\Requests\RequestActualizarEstado;
use App\Http\Requests\RequestActualizarTrackingEliminarFactura;
use App\Http\Requests\RequestActualizarTrackingSubirFactura;
use App\Http\Requests\RequestTrackingRegistro;
use App\Models\Cliente;
use App\Models\Enum\TipoHistorialTracking;
use App\Models\EstadoMBox;
use App\Models\Imagen;
use App\Models\Proveedor;
use App\Models\Tracking;
use App\Models\TrackingHistorial;
use DateTime;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\RequestCrearPrealerta;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemException;

class ServicioTracking
{
    private static function RetornaValorAtributo($atributos, $nombre)
    {
        foreach ($atributos as $atributo) {
            if ($atributo['l'] === $nombre) {
                return $atributo['val'];
            }
        }

        return null;
    }

    public static function ObtenerORegistrarTracking(RequestTrackingRegistro $request): ?Tracking
    {
        // 1. Ocupo verificar si el tracking ya existe en la BD
        // 1.1 Si existe, retornarlo
        // 2. Llamar a la API de parcelsApp para que retorne t#do el historial
        try {

            // 1. Ocupo verificar si el tracking ya existe en la BD
            $tracking = Tracking::where('IDTRACKING', $request->input('idTracking'))->first();

            // 1.1 Si existe, retornarlo
            if ($tracking) {
                return $tracking;
            }

            // 2. Llamar a la API de parcelsApp para que retorne t#do el historial
            $trackingNuevo = ServicioTracking::ConstruirTrackingCompleto($request->idTracking, $request->idCliente);

            return $trackingNuevo;
        } catch (Exception $e) {

            Log::error('[ServicioTracking->ObtenerORegistrarTracking] error:' . $e);

            return null;
        }
    }

    public static function ConstruirTracking($dataParcelsApp, $idCliente): ?Tracking
    {
        // 1. Recibir la data de parcelsApp
        // 2. Crear el objeto tracking

        try {

            $direccionPrincipalCliente = Cliente::find($idCliente)->direccionPrincipal;
            $estadoSinPreealerta = ServicioEstadoMBox::ObtenerEstadosMBox(['Sin Prealertar'])[0];
            $shipment = $dataParcelsApp['shipments'][0];
            $attributes = $shipment['attributes'];
            $arrayCarries = $shipment['carriers'];

            $tracking = new Tracking;
            $tracking->IDAPI = $datosRespuesta['uuid'] ?? 0; // por si ponemos despues el UUID
            $tracking->IDTRACKING = $shipment['trackingId'];
            $tracking->DESCRIPCION = null;
            $tracking->DESDE = ServicioTracking::RetornaValorAtributo($attributes, 'from') ?? '';
            $tracking->HASTA = ServicioTracking::RetornaValorAtributo($attributes, 'to') ?? '';
            $tracking->DESTINO = ServicioTracking::RetornaValorAtributo($attributes, 'destination') ?? '';
            $tracking->COURIER = implode(', ', $arrayCarries);
            $tracking->DIASTRANSITO = ServicioTracking::RetornaValorAtributo($attributes, 'days_transit') ?? 0;
            $tracking->PESO = 0.000;
            $tracking->IDDIRECCION = $direccionPrincipalCliente->id;
            $tracking->IDUSUARIO = Auth::user()->id ?? 1;
            $tracking->ESTADOMBOX = $estadoSinPreealerta->DESCRIPCION;
            $tracking->ESTADOSINCRONIZADO = $estadoSinPreealerta->DESCRIPCION;
            //
            $tracking->save();

            // Log::info('Tracking listo para ret: ' . $tracking);
            return $tracking;

        } catch (Exception $e) {

            Log::error('[ServicioTracking->ConstruirTracking] error:' . $e);

            return null;
        }

    }

    public static function ConstruirHistoriales($dataParcelsApp, $tracking): ?Collection
    {
        // 1. Obtener la data parseada de ParcelsApp
        // 2. Convertirla en los arrayCouriers y shipments que necesitamos
        // 3. Crear los historiales Tracking

        try {

            $shipment = $dataParcelsApp['shipments'][0];
            $arrayCarries = $shipment['carriers'];

            $historiales = [];
            if (!empty($shipment['states'])) {
                foreach ($shipment['states'] as $state) {
                    $courierCodigoJson = $state['carrier'];
                    $lugar = ServicioParcelsApp::SeparaLugar(!empty($state['location']) ? $state['location'] : '');

                    $historial = new TrackingHistorial;
                    $historial->DESCRIPCION = $state['status'];
                    $historial->DESCRIPCIONMODIFICADA = '';
                    $historial->PAISESTADO = $lugar[0];
                    $historial->CODIGOPOSTAL = $lugar[1];
                    $historial->OCULTADO = false;
                    $historial->TIPO = TipoHistorialTracking::API->value;
                    $historial->created_at = (new DateTime($state['date']))->format('Y-m-d H:i:s');
                    $historial->updated_at = (new DateTime($state['date']))->format('Y-m-d H:i:s');
                    $historial->IDCOURIER = $tracking->courrierNombreAId($arrayCarries[$courierCodigoJson]);
                    $historial->IDTRACKING = $tracking->id;

                    $historiales[] = $historial;
                }
            }

            foreach ($historiales as $historial) {
                $historial->save();
            }

            $usuario = ServicioCliente::ObtenerCliente($tracking);
            if (!empty($usuario)) {
                EventoClienteEnlazadoPaquete::dispatch($tracking, $usuario);
            }

            // Log::info('[ServicioTracking->ConstruirHistoriales] Historiales antes de ser pasados a collection: ' . json_encode($historiales));
            return Collection::make($historiales);

        } catch (Exception $e) {

            Log::error('[ServicioTracking->ConstruirHistoriales] error: ' . $e);

            return null;
        }
    }

    public static function ConstruirTrackingCompleto($idTracking, $idCliente): ?Tracking
    {
        try {
            $respuestaObjeto = ServicioParcelsApp::ObtenerTracking($idTracking);

            if (!$respuestaObjeto) {
                throw new Exception('Respuesta inválida/sin datos/el tracking no se encontró');
            }

            $tracking = ServicioTracking::ConstruirTracking($respuestaObjeto, $idCliente);

            if (!$tracking) {
                throw new Exception('No se pudo construir el tracking');
            }

            $historiales = ServicioTracking::ConstruirHistoriales($respuestaObjeto, $tracking);

            if (!$historiales) {
                throw new Exception('No se pudieron construir los historiales');
            }

            $tracking->historialesT = $historiales;
            Log::info('[ServicioTracking->ConstruirTrackingCompleto] Tracking completo: ' . $tracking);

            return $tracking;

        } catch (Exception $e) {

            Log::error('[ServicioTracking->ConstruirTrackingCompleto] error:' . $e);

            return null;
        }
    }

    /**
     * @param $request
     * @return void
     */
    public static function ActualizarTracking($request): void
    { //CAMBIAR REQUEST POR MAS LIMPIO
        // 1. Comparacion Estados

        // 2. Actualizar las imagenes
        // 2.1. Solo guardar las que son propias
        // 2.2. Almacenar los ids de todas las imagenes propias
        // 2.3. Si hay idsQue no se obtuvieron, hay que eliminarlos de la bd y del servidor

        // 3. Actualizar los historialesTrackings

        // 4. Actualizar los datos del encabezado

        $imagenesPropias = [];

        // 1. Ver el estado que trae por el usuario y obtener el trackingViejo
        $trackingViejo = Tracking::findOrFail($request->id);
        self::ComparacionEstados($request);

        // 2. Actualizar las imagenes
        $imagenesDatos = $request->input('imagenes', []); // id, archivoPropio, etc.

        foreach ($imagenesDatos as $index => $datos) {
            // 2.1. Solo guardar las que son propias y los nuevos (id < 0)
            $id = $datos['id'] ?? null;
            $archivoPropio = $datos['archivoPropio'] ?? null;
            if ($archivoPropio && $id) {
                if ($id < 0) {
                    $fileInRequest = "imagenes.$index.archivo"; // nombre exacto que espera GuardarArchivo
                    $path = 'trackings/' . $request->idTracking;
                    $rutaGuardada = self::GuardarArchivo($fileInRequest, $path, $request);
                    Log::info("[ST, AT] Archivo guardado correctamente: $rutaGuardada");

                    //guardarlo en registro imagen
                    /*$archivo = $request->file($fileInRequest);
                    $filename = str_replace(' ', '_', $archivo->getClientOriginalName());
                    $pathFile = $path . '/' . $filename;*/

                    $imagen = Imagen::create([
                        'RUTA' => $rutaGuardada,
                        'IDTRACKING' => $trackingViejo->id,
                    ]);

                    // 2.2. Almacenar los ids de todas las imagenes propias
                    $imagenesPropias[] = $imagen->id;
                } elseif ($id > 0) {
                    // 2.2. Almacenar los ids de todas las imagenes propias
                    $imagenesPropias[] = $id;
                }
            }
        }

        // 2.3. Si hay idsQue no se obtuvieron, hay que eliminarlos de la bd y del servidor
        $imagenesEliminadas = Imagen::whereNotIn('id', $imagenesPropias)->get();

        foreach ($imagenesEliminadas as $imagen) {
            Storage::disk('do')->delete($imagen->RUTA);
            Log::info('[ST, AT] Se elimino: ' . $imagen->RUTA);
            $imagen->delete();
        }

        // 3. Actualizar los historialesTrackings
        ServicioHistorialTracking::ActualizarHistoriales($request);

        // 4. Actualizar los datos del encabezado
        $proveedor = Proveedor::find($request->idProveedor);
        Log::info($proveedor && $proveedor->NOMBRE == 'MiLocker');
        if ($proveedor && $proveedor->NOMBRE == 'MiLocker') {
            Log::info('ES MILOCKER');
            $trackingProveedor = $trackingViejo->trackingProveedor;
            $trackingProveedor->TRACKINGPROVEEDOR = $request->trackingProveedor;
            $trackingProveedor->save();
        }

        $trackingViejo->OBSERVACIONES = $request->observaciones;
        $trackingViejo->IDDIRECCION = $request->idDireccion;
        $trackingViejo->save();
    }

    private static function GuardarArchivo(string $fileInRequest, string $path, $request): string
    {
        if (!$request->hasFile($fileInRequest)) {
            throw new FileNotFoundException('No se encontró el archivo enviado por el cliente.');
        }

        try {
            $archivo = $request->file($fileInRequest);
            $filename = str_replace(' ', '_', $archivo->getClientOriginalName());
            $rutaCompleta = $path . '/' . $filename;

            // 👉 Guardar el archivo primero
            $content = file_get_contents($archivo->getPathname());
            Storage::disk('do')->put($rutaCompleta, $content);
            // 👉 Luego retornar la ruta (ya guardado)
            return $rutaCompleta;

        } catch (FileNotFoundException $e) {
            Log::warning('Archivo no encontrado: ' . $e->getMessage());
            throw $e; // Propagamos el mismo tipo
        } catch (FilesystemException $e) {
            Log::error('Error de sistema de archivos: ' . $e->getMessage());
            throw new ExceptionArchivosDO('Hubo un error al guardar los archivos.');
        } catch (Exception $e) {
            Log::error('Error inesperado: ' . $e->getMessage());
            throw new ExceptionArchivosDO('Hubo un error al guardar los archivos.');
        }
    }

    /**
     * @throws ConnectionException
     * @throws ExceptionAPRequestRegistrarPrealerta
     * @throws ExceptionAPCouriersNoObtenidos
     * @throws ExceptionAPTokenNoObtenido
     * @throws ExceptionAPCourierNoObtenido
     * @throws ExceptionPrealertaNotFound
     * @throws ExceptionTrackingProveedorNotFound
     * @throws QueryException
     * @throws ExceptionAPRequestEliminarPrealerta
     * @throws ExceptionAPRequestActualizarPrealerta
     */
    public static function ComparacionEstados($request){
        // -Comparacion de estados
        // 1. Ver el estado que trae por el usuario y obtener el trackingViejo
        // 1.1. Si el estado se mantiene SPR pero con idProveedor, valor, descripcion: crear prealerta
        // 1.2. Si el estado pasa de PDO a SPR: eliminar prealerta
        // 1.3. Si el estado pasa de PDO a PDO: actualizar prealerta

        // 1. Ver el estado que trae por el usuario y obtener el trackingViejo
        $trackingViejo = Tracking::findOrFail($request->id);
        Log::info('[ST, CE] Entra: ');
        // 1.1. Si el estado pasa de SPR a PDO: crear prealerta
        $proveedor = Proveedor::find($request->idProveedor);

        if($trackingViejo->estadoMBox->ORDEN == 1 && $request->ordenEstatusSincronizado == 1 && $proveedor && $request->valorPrealerta > 0 && $request->descripcion && $request->descripcion != ''){ // esto no lo hago porque el request vlida
            Log::info('SPR a PDO');
            $data = [
                'valor' => $request->valorPrealerta,
                'descripcion' => $request->descripcion,
                'idProveedor' => $request->idProveedor,
                'idTracking' => $trackingViejo->IDTRACKING,
            ];

            // Crear un Request base
            $baseRequest = Request::create('/ruta', 'POST', $data);

            // Crear la instancia del Form Request a partir del request base
            $requestCrearPrealerta = RequestCrearPrealerta::createFromBase($baseRequest);

            // Llamar al mét#do
            ServicioPrealerta::RegistrarPrealerta($requestCrearPrealerta);
        }
        // 1.2. Si el estado pasa de PDO a SPR: eliminar prealerta
        //este no deberia de ocurrir
        elseif ($trackingViejo->estadoMBox->ORDEN == 2 && $request->ordenEstatusSincronizado == 2 && (!$proveedor || (!$request->descripcion || $request->descripcion == ''))){
            Log::info('PDO a SPR');
            ServicioPrealerta::EliminarPrealerta($trackingViejo->IDTRACKING);
        }
        // 1.3. Si el estado pasa de PDO a PDO: actualizar prealerta
        elseif ($trackingViejo->estadoMBox->ORDEN == 2 && $request->ordenEstatusSincronizado == 2){
            Log::info('PDO a PDO');
            ServicioPrealerta::ActualizarPrealerta($trackingViejo->IDTRACKING, $request->descripcion ,$request->valorPrealerta, $request->idProveedor);
        }
    }

    public static function ActualizarEstado(RequestActualizarEstado $request): Tracking{
        // 1. Validar que no entre un estado SPR(1) ni PDO(2) -Lo valida el RequestActualizarEstado
        // 2. Si el proveedor es Aeropost, actualizar solo el status
        // 3. Si el proveedor es MiLocker, actualizaa el status y el statusSincronizado
        $proveedor = Proveedor::findOrFail($request->idProveedor);
        $tracking = Tracking::findOrFail($request->id);
        $estadoAModificar = EstadoMBox::where('ORDEN', $request->ordenEstado)->firstOrFail();
        // 2. Si el proveedor es Aeropost, actualizar solo el status
        if ($proveedor->NOMBRE == 'Aeropost') {
            $tracking->ESTADOMBOX = $estadoAModificar->DESCRIPCION;
        }
        // 3. Si el proveedor es MiLocker, actualizaa el status y el statusSincronizado
        else if($proveedor->NOMBRE == 'MiLocker'){
            $tracking->ESTADOMBOX = $estadoAModificar->DESCRIPCION;
            $tracking->ESTADOSINCRONIZADO = $estadoAModificar->DESCRIPCION;
        }

        $tracking->save();
        return $tracking;
    }

    /**
     * @throws FileNotFoundException
     * @throws ExceptionArchivosDO
     */
    public static function SubirFactura(RequestActualizarTrackingSubirFactura $request): Tracking
    {
        // 1. Subir la factura
        // 2. Ver si tracking tenia otra factura guardada
        // 2.1. Si tenia ya otra guardada, eliminar la anterior
        // 2.2. Guardar la factura en tracking
        // 3. Poner el estadoSincronizado y estadoMBOX en FDO
        $tracking = Tracking::findOrFail($request->id);

        // 1. Subir la factura
        $path = 'trackings/' . $tracking->IDTRACKING. '/factura';
        $urlFactura = self::GuardarArchivo('factura', $path ,$request);

        // 2. Ver si tracking tenia otra factura guardada

        if ($tracking->RUTAFACTURA){
            // 2.1. Si tenia ya otra guardada, eliminar la anterior
            Storage::disk('do')->delete($tracking->RUTAFACTURA);
        }

        // 2.2. Guardar la factura en tracking
        $tracking->RUTAFACTURA = $urlFactura;

        // 3. Poner el estadoSincronizado y estadoMBOX en FDO
        $tracking->ESTADOMBOX = 'Facturado';
        $tracking->ESTADOSINCRONIZADO = 'Facturado';

        $tracking->save();
        return $tracking;
    }

    public static function EliminarFactura(RequestActualizarTrackingEliminarFactura $request): Tracking
    {
        // 1. Eliminar la factura
        // 2. Poner el estadoSincronizado y estadoMBOX en EN

        $tracking = Tracking::findOrFail($request->id);

        // 1. Eliminar la factura
        if ($tracking->RUTAFACTURA){
            // 2.1. Si tenia ya otra guardada, eliminar la anterior
            Storage::disk('do')->delete($tracking->RUTAFACTURA);
        }

        // 2.2. Guardar la factura en tracking
        $tracking->RUTAFACTURA = null;

        // 2. Poner el estadoSincronizado y estadoMBOX en EN
        $tracking->ESTADOMBOX = 'Entregado';
        $tracking->ESTADOSINCRONIZADO = 'Entregado';

        $tracking->save();
        return $tracking;
    }
}
