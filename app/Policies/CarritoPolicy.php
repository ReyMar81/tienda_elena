<?php

namespace App\Policies;

use App\Models\Carrito;
use App\Models\User;

class CarritoPolicy
{
    /**
     * Determine si el usuario puede ver cualquier carrito
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver su carrito
        return true;
    }

    /**
     * Determine si el usuario puede ver el carrito
     */
    public function view(User $user, Carrito $carrito): bool
    {
        // Solo el propietario del carrito puede verlo
        return $carrito->user_id === $user->id;
    }

    /**
     * Determine si el usuario puede crear un carrito
     */
    public function create(User $user): bool
    {
        // Todos los usuarios autenticados pueden crear/usar su carrito
        return true;
    }

    /**
     * Determine si el usuario puede actualizar el carrito
     */
    public function update(User $user, Carrito $carrito): bool
    {
        // Solo el propietario del carrito puede modificarlo
        return $carrito->user_id === $user->id;
    }

    /**
     * Determine si el usuario puede eliminar el carrito
     */
    public function delete(User $user, Carrito $carrito): bool
    {
        // Solo el propietario del carrito puede eliminarlo
        return $carrito->user_id === $user->id;
    }

    /**
     * Método auxiliar para verificar acceso por ruta
     */
    private function userHasAccessToRoute(User $user, string $routeName): bool
    {
        // Validar que el usuario tenga role_id
        if (!$user->role_id) {
            return false;
        }

        $menuItem = \App\Models\MenuItem::where('ruta_laravel', $routeName)
            ->where('role_id', $user->role_id)
            ->first();

        return $menuItem !== null;
    }
}
