<?php

namespace App\Http\Resources;

use App\Models\Enum\TipoImagen;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DetalleImagenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'archivo' => $this->TIPOIMAGEN == TipoImagen::Propia->value ?
                Storage::disk('do')->temporaryUrl(
                $this->RUTA,
                now()->addMinutes(10)
                )
                :
                $this->RUTA
            ,
            'tipoImagen' => $this->TIPOIMAGEN,
        ];
    }
}
