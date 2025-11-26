<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cuota_id' => 'required|exists:cuotas,id',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago_id' => 'required|exists:metodos_pago,id',
            'fecha' => 'nullable|date',
            'comprobante' => 'nullable|string|max:100',
            'notas' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'cuota_id.required' => 'La cuota es obligatoria.',
            'cuota_id.exists' => 'La cuota seleccionada no existe.',
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número válido.',
            'monto.min' => 'El monto debe ser mayor a 0.',
            'metodo_pago_id.required' => 'El método de pago es obligatorio.',
            'metodo_pago_id.exists' => 'El método de pago seleccionado no existe.',
            'fecha.date' => 'La fecha de pago debe ser válida.',
            'comprobante.string' => 'El comprobante debe ser texto válido.',
            'comprobante.max' => 'El número de comprobante no puede exceder :max caracteres.',
            'notas.string' => 'Las notas deben ser texto válido.',
            'notas.max' => 'Las notas no pueden exceder :max caracteres.',
        ];
    }

    /**
     * Nombres personalizados de atributos en español
     */
    public function attributes(): array
    {
        return [
            'cuota_id' => 'cuota',
            'monto' => 'monto',
            'metodo_pago_id' => 'método de pago',
            'fecha' => 'fecha de pago',
            'comprobante' => 'comprobante',
            'notas' => 'notas',
        ];
    }
}
