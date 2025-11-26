<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePromocionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'valor_descuento_decimal' => 'sometimes|required|numeric|min:0|max:100',
            'fecha_inicio' => 'sometimes|required|date',
            'fecha_fin' => 'sometimes|required|date|after:fecha_inicio',
            'estado' => 'boolean',
            'productos' => 'nullable|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.aplica_mayorista' => 'boolean',
            'productos.*.aplica_minorista' => 'boolean',
            'categorias' => 'nullable|array',
            'categorias.*.id' => 'required|exists:categorias,id',
            'categorias.*.aplica_mayorista' => 'boolean',
            'categorias.*.aplica_minorista' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la promoción es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto válido.',
            'nombre.max' => 'El nombre no puede exceder :max caracteres.',
            'descripcion.string' => 'La descripción debe ser texto válido.',
            'descripcion.max' => 'La descripción no puede exceder :max caracteres.',
            'valor_descuento_decimal.required' => 'El porcentaje de descuento es obligatorio.',
            'valor_descuento_decimal.numeric' => 'El descuento debe ser un número válido.',
            'valor_descuento_decimal.min' => 'El descuento debe ser mayor o igual a 0.',
            'valor_descuento_decimal.max' => 'El descuento no puede ser mayor a 100%.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'estado.boolean' => 'El estado debe ser verdadero o falso.',
            'productos.array' => 'Los productos deben ser un conjunto válido.',
            'productos.*.id.required' => 'El ID del producto es obligatorio.',
            'productos.*.id.exists' => 'Uno o más productos seleccionados no existen.',
            'productos.*.aplica_mayorista.boolean' => 'El campo aplica mayorista debe ser verdadero o falso.',
            'productos.*.aplica_minorista.boolean' => 'El campo aplica minorista debe ser verdadero o falso.',
            'categorias.array' => 'Las categorías deben ser un conjunto válido.',
            'categorias.*.id.required' => 'El ID de la categoría es obligatorio.',
            'categorias.*.id.exists' => 'Una o más categorías seleccionadas no existen.',
            'categorias.*.aplica_mayorista.boolean' => 'El campo aplica mayorista debe ser verdadero o falso.',
            'categorias.*.aplica_minorista.boolean' => 'El campo aplica minorista debe ser verdadero o falso.',
        ];
    }

    /**
     * Nombres personalizados de atributos en español
     */
    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'descripcion' => 'descripción',
            'valor_descuento_decimal' => 'porcentaje de descuento',
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin' => 'fecha de fin',
            'estado' => 'estado',
            'productos' => 'productos',
            'categorias' => 'categorías',
        ];
    }
}
