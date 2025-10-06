<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestObtenerCodigoPostal extends FormRequest
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
            'id' => 'required|exists:distritos,id',
        ];
    }

    /**
     * 👇 Esto permite que Laravel valide también los parámetros de ruta.
     */
    public function validationData()
    {
        return array_merge($this->all(), $this->route()->parameters());
    }
}
