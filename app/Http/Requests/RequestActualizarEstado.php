<?php

namespace App\Http\Requests;

use App\Models\EstadoMBox;
use App\Models\Tracking;
use \Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;

class RequestActualizarEstado extends FormRequest
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
            'ordenEstado' => [
                'required',
                'exists:estadombox,ORDEN',
                function ($attribute, $value, $fail) {
                    // Buscar el estado correspondiente
                    $estadoMBox = EstadoMBox::where('ORDEN', $value)->first();

                    // Validar la descripción
                    if ($estadoMBox && in_array($estadoMBox->DESCRIPCION, ['Sin Prealertar', 'Prealertado'])) {
                        $fail('No se puede actualizar el estado a "Sin Prealertar" o "Prealertado".');
                    }
                },
            ],
            'idProveedor' => [
                'required',
                'exists:proveedor,id',
                function ($attribute, $value, $fail) {
                    // Obtener el tracking según el id enviado
                    $tracking = Tracking::find($this->id);
                    $trackingProveedor = optional($tracking)->trackingProveedor;
                    // Si existe y el proveedor no coincide
                    if ($tracking && $trackingProveedor && $trackingProveedor->IDPROVEEDOR != $value) {
                        $fail('El proveedor no coincide con el proveedor actual del tracking.');
                    }
                },
            ],
        ];
    }
}
