<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestRegistrarUsuario extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cedula' => [
                'required',
                'integer',
                Rule::unique('users', 'CEDULA'), // <-- Ignora el ID que pasas
                'max:1000000000',
                'min:19999999',
            ],

            'nombre' => ['required', 'string', 'max:55'],
            'apellidos' => ['required', 'string', 'max:38'],
            'empresa' => ['nullable', 'string', 'max:110'],
            'telefono' => ['required', 'integer', 'max:99999999', 'min:1000000'],
            'correo' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
        ];
    }
}
