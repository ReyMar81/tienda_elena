<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearClienteRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'ci' => 'required|string|unique:users,ci|max:20',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser texto válido.',
            'name.max' => 'El nombre no puede exceder :max caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'ci.required' => 'La cédula de identidad es obligatoria.',
            'ci.string' => 'La cédula debe ser texto válido.',
            'ci.unique' => 'Esta cédula de identidad ya está registrada.',
            'ci.max' => 'La cédula no puede exceder :max caracteres.',
            'telefono.string' => 'El teléfono debe ser texto válido.',
            'telefono.max' => 'El teléfono no puede exceder :max caracteres.',
            'direccion.string' => 'La dirección debe ser texto válido.',
            'direccion.max' => 'La dirección no puede exceder :max caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'ci' => 'cédula de identidad',
            'telefono' => 'teléfono',
            'direccion' => 'dirección',
        ];
    }
}
