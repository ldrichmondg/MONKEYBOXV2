<?php

namespace App\Http\Resources;

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
            'archivo' => Storage::disk('do')->temporaryUrl(
                $this->RUTA,
                now()->addMinutes(10)
            ),
            'archivoPropio' => true, //por el momento
        ];
    }
}
