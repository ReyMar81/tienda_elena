<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'metodo_pago_id' => 'required|exists:metodo_pago,id',
            'tipo_venta' => 'required|in:contado,credito',
            // Campos para crédito
            'meses_plazo' => 'required_if:tipo_venta,credito|nullable|integer|min:1|max:36',
            'tasa_interes' => 'required_if:tipo_venta,credito|nullable|numeric|min:0|max:100',
            'fecha_primer_pago' => 'required_if:tipo_venta,credito|nullable|date|after:today',
        ];
    }

    public function messages(): array
    {
        return [
            'metodo_pago_id.required' => 'El método de pago es obligatorio.',
            'metodo_pago_id.exists' => 'El método de pago seleccionado no existe.',
            'tipo_venta.required' => 'El tipo de venta es obligatorio.',
            'tipo_venta.in' => 'El tipo de venta debe ser contado o crédito.',
            'meses_plazo.required_if' => 'Los meses de plazo son obligatorios para ventas a crédito.',
            'meses_plazo.integer' => 'Los meses de plazo deben ser un número entero.',
            'meses_plazo.min' => 'Los meses de plazo deben ser al menos 1.',
            'meses_plazo.max' => 'Los meses de plazo no pueden exceder 36.',
            'tasa_interes.required_if' => 'La tasa de interés es obligatoria para ventas a crédito.',
            'tasa_interes.numeric' => 'La tasa de interés debe ser un número.',
            'tasa_interes.min' => 'La tasa de interés no puede ser negativa.',
            'tasa_interes.max' => 'La tasa de interés no puede exceder 100%.',
            'fecha_primer_pago.required_if' => 'La fecha del primer pago es obligatoria para ventas a crédito.',
            'fecha_primer_pago.date' => 'La fecha del primer pago debe ser una fecha válida.',
            'fecha_primer_pago.after' => 'La fecha del primer pago debe ser posterior a hoy.',
        ];
    }

    public function attributes(): array
    {
        return [
            'metodo_pago_id' => 'método de pago',
            'tipo_venta' => 'tipo de venta',
            'meses_plazo' => 'meses de plazo',
            'tasa_interes' => 'tasa de interés',
            'fecha_primer_pago' => 'fecha del primer pago',
        ];
    }
}
