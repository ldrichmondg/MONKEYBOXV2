<?php

namespace App\Services\Proveedores;

use App\Models\Prealerta;

interface InterfazProveedor
{
    // T#do proveedor tiene que permitir hacer lo siguiente:
    // - CRUD Prealerta
    // 1. Registrar prealerta
    // 2. Actualizar prealerta
    // 3. Eliminar Prealerta

    // 4. Obtener los Couriers si es que se necesita para la prealerta
    // - Sincronizar paquetes con la app
    // 5. Sincronizar Encabezado todos los trackings o los indicados
    // 6. Sincronizar los Historiales todos los trackings o los indicados
    // 7. Sincronizar attachments todos los trackings o los indicados
    // 8. Sincronizar completo el tracking o los indicados

    public function RegistrarPrealerta($tracking, $valor, $descripcion, $idProveedor): Prealerta;
    public function ActualizarPrealerta($idPrealerta, $descripcion, $valor, $numeroTracking, $nombreTienda, $courierId, $consigneeId): void;
    public function EliminarPrealerta($idPrealerta): void;
    public function ObtenerCourier($idTracking): array; //array porque generalmente viene como objeto

    public function SincronizarEncabezadoTrackings(array $numerosTracking);

    //public function SincronizarHistorialTrackings(?array $numerosTracking);

    //public function SincronizarAttachmentsTrackings(?array $numerosTracking);
    public function SincronizarCompletoTrackings(array $numerosTracking); //diferencia entre este metodo es que es una combinacion de los anteriores pero brindar la posibilidad de solicitar solo un request, en vez de un request x attachments, encabezado e historial


}
