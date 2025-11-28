<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePedidoRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'metodo_pago_id' => 'required|exists:metodos_pago,id',
            'tipo_pago' => 'required|in:contado,credito',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'confirmar_inmediatamente' => 'boolean',
            'numero_cuotas' => 'required_if:confirmar_inmediatamente,true|nullable|integer|min:1|max:12',
            'tasa_interes' => 'nullable|numeric|min:0|max:100',
            'descuento_percent' => 'nullable|numeric|min:0|max:100',
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Debe seleccionar un cliente',
            'metodo_pago_id.required' => 'Debe seleccionar un método de pago',
            'tipo_pago.required' => 'Debe seleccionar el tipo de pago',
            'detalles.required' => 'Debe agregar al menos un producto',
            'detalles.min' => 'Debe agregar al menos un producto',
            'numero_cuotas.required_if' => 'Debe especificar el número de cuotas para pagos a crédito',
            'numero_cuotas.min' => 'El número de cuotas debe ser al menos 1',
            'numero_cuotas.max' => 'El número de cuotas no puede ser mayor a 12',
            'tasa_interes.numeric' => 'El valor de la tasa de interés debe ser numérico',
            'tasa_interes.min' => 'La tasa de interés no puede ser negativa',
            'tasa_interes.max' => 'La tasa de interés no puede ser mayor al 100%',
            'descuento_percent.numeric' => 'El descuento debe ser un número válido',
            'descuento_percent.min' => 'El descuento no puede ser negativo',
            'descuento_percent.max' => 'El descuento no puede ser mayor al 100%'
        ];
    }
}
