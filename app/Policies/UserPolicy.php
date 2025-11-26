<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        // Solo Propietario puede ver la lista de usuarios
        return $user->tieneRol('Propietario');
    }

    /**
     * Determine if the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Propietario puede ver cualquier usuario
        // Usuarios pueden verse a sí mismos
        return $user->tieneRol('Propietario') || $user->id === $model->id;
    }

    /**
     * Determine if the user can create users.
     */
    public function create(User $user): bool
    {
        // Solo Propietario puede crear usuarios
        return $user->tieneRol('Propietario');
    }

    /**
     * Determine if the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Propietario puede actualizar cualquier usuario
        // Usuarios pueden actualizarse a sí mismos (perfil)
        return $user->tieneRol('Propietario') || $user->id === $model->id;
    }

    /**
     * Determine if the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Solo Propietario puede eliminar usuarios
        // No puede eliminarse a sí mismo
        return $user->tieneRol('Propietario') && $user->id !== $model->id;
    }

    /**
     * Determine if the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->tieneRol('Propietario');
    }

    /**
     * Determine if the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->tieneRol('Propietario') && $user->id !== $model->id;
    }
}

