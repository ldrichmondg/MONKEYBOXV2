<?php

namespace App\Http\Resources;

use \Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TrackingDetalleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $idProveedor = optional($this->trackingProveedor)->IDPROVEEDOR;
        $nombreProveedor = optional(optional($this->trackingProveedor)->proveedor)->NOMBRE;
        $cliente = $this->direccion->cliente;

        return [
            'id' => $this->id,
            'idTracking' => $this->IDTRACKING,
            'nombreCliente' => $cliente->usuario->nombreCompletoDosApellidos(),
            'desde' => empty($this->DESDE) ? 'N/A' : $this->DESDE,
            'hasta' => empty($this->HASTA) ? 'N/A' : $this->HASTA,
            'destino' => empty($this->DESTINO) ? 'N/A' : $this->DESTINO,
            'couriers' => $this->COURIER,
            'diasTransito' => empty($this->DIASTRANSITO) ? 'N/A' : $this->DIASTRANSITO,
            'peso' => $this->PESO,
            'idProveedor' => $idProveedor == null ? -1 : $idProveedor,
            'nombreProveedor' => $nombreProveedor,
            'idCliente' => $cliente->id,
            'idDireccion' => $this->direccion->id,
            'observaciones' => empty($this->OBSERVACIONES) ? '' : $this->OBSERVACIONES,
            'estatus' => $this->ESTADOMBOX,
            'ordenEstatus' => $this->estadoMBox->ORDEN,
            'estatusSincronizado' => $this->ESTADOSINCRONIZADO,
            'ordenEstatusSincronizado' => $this->estadoSincronizado->ORDEN,
            'historialesTracking' => HistorialTrackingDetalleResource::collection($this->whenLoaded('historialesT'))->resolve(),
            'trackingProveedor' => optional($this->trackingProveedor)->TRACKINGPROVEEDOR,
            'valorPrealerta' => optional(optional($this->trackingProveedor)->prealerta)->VALOR !== null ? $this->trackingProveedor->prealerta->VALOR : 1.5,
            'descripcion' => optional(optional($this->trackingProveedor)->prealerta)->DESCRIPCION !== null ? $this->trackingProveedor->prealerta->DESCRIPCION : '',
            'cliente' => (new DetalleClienteTrackingResource($cliente))->resolve(),
            'imagenes' => DetalleImagenResource::collection($this->imagenes)->resolve(),
            'factura' => $this->ESTADOMBOX === 'Facturado' && !empty($this->RUTAFACTURA)
                ? Storage::disk('do')->temporaryUrl($this->RUTAFACTURA, now()->addMinutes(10))
                : null,
        ];
    }
}
