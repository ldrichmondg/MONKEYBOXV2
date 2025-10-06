<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestRegistroCliente extends RequestRegistrarUsuario //porque ocupo validar los mismos datos como si fuesen un usuario
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

        return array_merge(parent::rules(), [
            'casillero' => [
                'required',
                'string',
                'max:20',
                Rule::unique('cliente', 'CASILLERO'),
            ],
            'fechaNacimiento' => 'nullable|date|before:today',
            'direcciones' => ['required', 'array', 'min:1'],
            'direcciones.*.direccion' => ['required', 'string', 'max:235'],
            'direcciones.*.tipo' => ['required', 'integer', 'in:1,2'],
            'direcciones.*.codigoPostal' => ['required', 'integer', 'max:100000'],
            'direcciones.*.paisEstado' => ['required', 'string', 'max:70'],
            'direcciones.*.linkWaze' => ['nullable','url', 'max:250'],
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $direcciones = $this->input('direcciones', []);

            $tienePrincipal = collect($direcciones)
                ->contains(fn($dir) => isset($dir['tipo']) && $dir['tipo'] == 1);

            if (!$tienePrincipal) {
                $validator->errors()->add('direcciones', 'Debes tener al menos una dirección principal.');
            }
        });
    }

    public function messages()
    {
        return [
            'direcciones.required' => 'Debes ingresar al menos una dirección.',
            'direcciones.*.direccion.required' => 'El campo dirección es obligatorio.',
            'direcciones.*.tipo.required' => 'El tipo de dirección es obligatorio.',
            'direcciones.*.linkWaze.url' => 'El link de Waze debe ser una URL válida.',
        ];
    }
}
