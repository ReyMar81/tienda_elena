<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreProductoRequest - Validación para crear productos
 * 
 * Reglas de negocio:
 * - Código único en la BD
 * - Precio mayor a 0
 * - Stock inicial >= 0
 * - Categoría debe existir
 * - Imagen opcional
 */
class StoreProductoRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado (se usa Policy)
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en ProductoPolicy
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:50|unique:productos,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'precio_compra' => 'required|numeric|min:0.01|max:999999.99',
            'precio_venta' => 'required|numeric|min:0.01|max:999999.99|gt:precio_compra',
            'precio_venta_mayorista' => 'required|numeric|min:0.01|max:999999.99|gte:precio_compra|lte:precio_venta',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'marca' => 'nullable|string|max:100',
            'categoria_id' => 'nullable|exists:categorias,id',
            'estado' => 'boolean',
            'imagen' => 'nullable|array',
            'imagen.*' => 'image|mimes:jpeg,jpg,png,webp|max:2048',
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    public function messages(): array
    {
        return [
            'codigo.required' => 'El código del producto es obligatorio.',
            'codigo.unique' => 'Este código ya está registrado.',
            'codigo.max' => 'El código no puede exceder :max caracteres.',
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder :max caracteres.',
            'descripcion.max' => 'La descripción no puede exceder :max caracteres.',
            'precio_compra.required' => 'El precio de compra es obligatorio.',
            'precio_compra.numeric' => 'El precio de compra debe ser un número válido.',
            'precio_compra.min' => 'El precio de compra debe ser mayor a 0.',
            'precio_compra.max' => 'El precio de compra no puede exceder :max.',
            'precio_venta.required' => 'El precio de venta es obligatorio.',
            'precio_venta.numeric' => 'El precio de venta debe ser un número válido.',
            'precio_venta.min' => 'El precio de venta debe ser mayor a 0.',
            'precio_venta.max' => 'El precio de venta no puede exceder :max.',
            'precio_venta.gt' => 'El precio de venta debe ser mayor al precio de compra.',
            'precio_venta_mayorista.required' => 'El precio mayorista es obligatorio.',
            'precio_venta_mayorista.numeric' => 'El precio mayorista debe ser un número válido.',
            'precio_venta_mayorista.min' => 'El precio mayorista debe ser mayor a 0.',
            'precio_venta_mayorista.max' => 'El precio mayorista no puede exceder :max.',
            'precio_venta_mayorista.gte' => 'El precio mayorista debe ser mayor o igual al precio de compra.',
            'precio_venta_mayorista.lte' => 'El precio mayorista no puede ser mayor al precio de venta.',
            'stock_actual.required' => 'El stock actual es obligatorio.',
            'stock_actual.integer' => 'El stock actual debe ser un número entero.',
            'stock_actual.min' => 'El stock actual no puede ser negativo.',
            'stock_minimo.required' => 'El stock mínimo es obligatorio.',
            'stock_minimo.integer' => 'El stock mínimo debe ser un número entero.',
            'stock_minimo.min' => 'El stock mínimo no puede ser negativo.',
            'marca.max' => 'La marca no puede exceder :max caracteres.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'estado.boolean' => 'El estado debe ser verdadero o falso.',
            'imagen.array' => 'Las imágenes deben ser un conjunto de archivos.',
            'imagen.*.image' => 'Cada archivo debe ser una imagen válida.',
            'imagen.*.mimes' => 'Las imágenes deben ser formato: jpeg, jpg, png o webp.',
            'imagen.*.max' => 'Cada imagen no puede pesar más de 2MB.',
        ];
    }

    /**
     * Nombres personalizados de atributos en español
     */
    public function attributes(): array
    {
        return [
            'codigo' => 'código',
            'nombre' => 'nombre',
            'descripcion' => 'descripción',
            'precio_compra' => 'precio de compra',
            'precio_venta' => 'precio de venta',
            'precio_venta_mayorista' => 'precio mayorista',
            'stock_actual' => 'stock actual',
            'stock_minimo' => 'stock mínimo',
            'marca' => 'marca',
            'categoria_id' => 'categoría',
            'estado' => 'estado',
            'imagen' => 'imagen',
        ];
    }
}
