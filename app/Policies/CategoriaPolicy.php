<?php

namespace App\Policies;

use App\Models\Categoria;
use App\Models\User;
use App\Models\MenuItem;

/**
 * CategoriaPolicy - Control de Acceso Basado en Menú Dinámico
 */
class CategoriaPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->userHasAccessToRoute($user, 'categorias.index');
    }

    public function view(User $user, Categoria $categoria): bool
    {
        return $this->userHasAccessToRoute($user, 'categorias.index');
    }

    public function create(User $user): bool
    {
        return $this->userHasAccessToRoute($user, 'categorias.create');
    }

    public function update(User $user, Categoria $categoria): bool
    {
        return $this->userHasAccessToRoute($user, 'categorias.edit');
    }

    public function delete(User $user, Categoria $categoria): bool
    {
        $hasDeletePermission = $this->userHasAccessToRoute($user, 'categorias.destroy');
        
        // No eliminar si tiene productos asociados
        $hasProductos = $categoria->productos()->count() > 0;
        
        return $hasDeletePermission && !$hasProductos;
    }

    private function userHasAccessToRoute(User $user, string $route): bool
    {
        $roleId = $user->role_id;
        
        if (!$roleId) {
            return false;
        }

        return MenuItem::where('role_id', $roleId)
            ->where('ruta_laravel', $route)
            ->exists();
    }
}
