<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('usuario')->id;

        return [
            'nombre' => 'required|string|min:2|max:255',
            'apellidos' => 'required|string|min:2|max:255',
            'ci' => ['required', 'string', 'min:5', 'max:20', Rule::unique('users', 'ci')->ignore($userId)],
            'telefono' => 'required|string|min:7|max:20',
            'fecha_nacimiento' => 'nullable|date|before:today|after:1900-01-01',
            'role_id' => 'required|integer|exists:roles,id',
            'estado' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos :min caracteres.',
            'nombre.max' => 'El nombre no puede exceder :max caracteres.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.min' => 'Los apellidos deben tener al menos :min caracteres.',
            'apellidos.max' => 'Los apellidos no pueden exceder :max caracteres.',
            'ci.required' => 'El CI es obligatorio.',
            'ci.min' => 'El CI debe tener al menos :min caracteres.',
            'ci.max' => 'El CI no puede exceder :max caracteres.',
            'ci.unique' => 'Este CI ya está registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.min' => 'El teléfono debe tener al menos :min dígitos.',
            'telefono.max' => 'El teléfono no puede exceder :max caracteres.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'fecha_nacimiento.after' => 'La fecha de nacimiento debe ser posterior a 1900.',
            'role_id.required' => 'Debe seleccionar un rol.',
            'role_id.integer' => 'El rol debe ser un valor válido.',
            'role_id.exists' => 'El rol seleccionado no existe.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.boolean' => 'El estado debe ser verdadero o falso.',
        ];
    }

    /**
     * Nombres personalizados de atributos en español
     */
    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'apellidos' => 'apellidos',
            'ci' => 'CI',
            'telefono' => 'teléfono',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'fecha_nacimiento' => 'fecha de nacimiento',
            'role_id' => 'rol',
            'estado' => 'estado',
        ];
    }
}
