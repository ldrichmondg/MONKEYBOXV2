<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestActualizarCliente extends RequestActualizarUsuario
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $idCliente = $this->input('id');

        // Intenta buscar el cliente
        $cliente = Cliente::find($idCliente);

        // Si existe, setea IDUSUARIO para usar en rules()
        $this->merge([
            'idUsuario' => $cliente ? $cliente->IDUSUARIO : null,
        ]);
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $idCliente = $this->input('id');
        $idUsuario = $this->input('idUsuario');

        return array_merge(parent::rules(), [
            'id' => ['required', 'integer', 'exists:cliente,id'], // <-- valida en tabla clientes
            'cedula' => [
                'required',
                'integer',
                Rule::unique('users', 'CEDULA')->ignore($idUsuario), // <-- Ignora el ID que pasas
                'max:1000000000',
                'min:19999999',
            ],
            'correo' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($idUsuario)
            ],
            'casillero' => [
                'required',
                'string',
                'max:20',
                Rule::unique('cliente', 'CASILLERO')->ignore($idCliente),
            ],
            'fechaNacimiento' => 'nullable|date|before:today',
            'direcciones' => ['required', 'array', 'min:1'],
            'direcciones.*.id' => ['required', 'integer'],
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
