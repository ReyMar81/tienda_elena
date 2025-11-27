<?php

namespace App\Policies;

use App\Models\Promocion;
use App\Models\User;
use App\Models\MenuItem;

class PromocionPolicy
{
    public function viewAny(User $user): bool
    {
        // Permitir a cualquier usuario autenticado listar promociones
        return true;
    }

    public function view(User $user, Promocion $promocion): bool
    {
        // Permitir a cualquier usuario autenticado ver detalles de promociones
        return true;
    }

    public function create(User $user): bool
    {
        return $this->userHasAccessToRoute($user, 'promociones.create');
    }

    public function update(User $user, Promocion $promocion): bool
    {
        return $this->userHasAccessToRoute($user, 'promociones.update');
    }

    public function delete(User $user, Promocion $promocion): bool
    {
        return $this->userHasAccessToRoute($user, 'promociones.destroy');
    }

    private function userHasAccessToRoute(User $user, string $route): bool
    {
        $roleId = $user->role_id;
        
        if (!$roleId) {
            return false;
        }

        // Buscar en menu_items si el rol del usuario tiene acceso a esta ruta
        return MenuItem::where('role_id', $roleId)
            ->where('ruta_laravel', $route)
            ->exists();
    }
}
