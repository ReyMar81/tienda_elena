<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePagoRequest extends FormRequest
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
            'cuota_id' => 'required|exists:cuotas,id',
            'metodo_pago_id' => 'required|exists:metodos_pago,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cuota_id.required' => 'Debe seleccionar una cuota',
            'cuota_id.exists' => 'La cuota seleccionada no existe',
            'metodo_pago_id.required' => 'Debe seleccionar un método de pago',
            'monto.required' => 'Debe ingresar el monto del pago',
            'monto.min' => 'El monto debe ser mayor a 0',
            'fecha.required' => 'Debe seleccionar la fecha del pago',
        ];
    }
}
