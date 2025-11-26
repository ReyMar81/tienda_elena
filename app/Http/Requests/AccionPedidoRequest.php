<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccionPedidoRequest extends FormRequest
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
            'accion' => 'required|in:confirmar,cancelar',
            'numero_cuotas' => 'nullable|integer|min:1|max:12',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'accion.required' => 'Debe especificar la acción a realizar',
            'accion.in' => 'La acción debe ser confirmar o cancelar',
            'numero_cuotas.required_if' => 'Debe especificar el número de cuotas',
            'numero_cuotas.min' => 'El número de cuotas debe ser al menos 1',
            'numero_cuotas.max' => 'El número de cuotas no puede ser mayor a 12',
        ];
    }
}
