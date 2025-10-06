<?php

namespace App\Http\Requests;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\Enum\TipoDirecciones;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class RequestActualizarTracking extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|exists:tracking,id',
            'peso' => 'required|numeric|min:0',
            'idDireccion' => 'required|exists:direccion,id',
            'observaciones' => 'max:495',
            'ordenEstatus' => 'required|exists:estadombox,ORDEN',
            'ordenEstatusSincronizado' => 'required|exists:estadombox,ORDEN',

            //historiales tracking
            'historialesTracking' => ['nullable', 'array'],
            'historialesTracking.*.id' => ['required', 'integer'],
            'historialesTracking.*.descripcion' => ['required', 'string', 'max:370'],
            'historialesTracking.*.descripcionModificada' => ['nullable', 'string', 'max:370'],
            'historialesTracking.*.codigoPostal' => ['required', 'integer', 'max:100000'],
            'historialesTracking.*.ocultado' => ['required', 'boolean'],
            'historialesTracking.*.tipo' => ['required', 'string', Rule::in(array_map(fn($case) => $case->id(), TipoDirecciones::cases())),],
            //imagenes
            'imagenes' => ['nullable', 'array'],
            'imagenes.*.id' => ['required', 'integer'],
            'imagenes.*.archivo' => ['required'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->sometimes('idProveedor', ['required', 'exists:proveedor,id'], function ($input) {
            // Aplica la regla solo si ordenEstatusSincronizado != 1
            return isset($input->ordenEstatusSincronizado) && $input->ordenEstatusSincronizado != 1;
        });

        $validator->sometimes('valorPrealerta', ['required', 'decimal:0,3'], function ($input) {
            // Aplica la regla solo si ordenEstatusSincronizado != 1
            return isset($input->ordenEstatusSincronizado) && $input->ordenEstatusSincronizado != 1;
        });

        $validator->sometimes('descripcion', ['required', 'string', 'max:250'], function ($input) {
            // Aplica la regla solo si ordenEstatusSincronizado != 1
            return isset($input->ordenEstatusSincronizado) && $input->ordenEstatusSincronizado != 1;
        });

        /*$validator->sometimes('imagenes.*.archivo', ['string'], function ($input) {
            // $input es el elemento 'imagenes.*'
            $idImagen = data_get($input, 'id'); // obtenemos el id de la imagen
            Log::info($idImagen);
            return $idImagen > 0; // si es >0 debe ser string
        });

        $validator->sometimes('imagenes.*.archivo', ['file', 'mimetypes:image/jpeg,image/png,image/jpg', 'max:2048'], function ($input) {
            $idImagen = data_get($input, 'id'); // obtenemos el id de la imagen
            Log::info($idImagen);
            return $idImagen < 0; // si es <0 debe ser archivo
        });*/
        //lo valido despues


    }
}
