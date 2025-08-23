<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestCrearPrealerta extends FormRequest
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
            'idTracking' => ['required', 'exists:tracking,IDTRACKING'],
            'valor' => ['required', 'decimal:0,3'],
            'descripcion' => ['required', 'string', 'max:250'],
            'idProveedor' => ['required', 'exists:proveedor,id'],
        ];
    }
}
