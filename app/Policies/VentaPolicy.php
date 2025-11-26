<?php

namespace App\Policies;

use App\Models\Venta;
use App\Models\User;

class VentaPolicy
{
    /**
     * Determine si el usuario puede ver cualquier venta
     */
    public function viewAny(User $user): bool
    {
        return $this->userHasAccessToRoute($user, 'ventas.index');
    }

    /**
     * Determine si el usuario puede ver la venta
     */
    public function view(User $user, Venta $venta): bool
    {
        // El cliente puede ver sus propias ventas
        if ($venta->user_id === $user->id) {
            return true;
        }

        // Propietario y vendedor pueden ver todas
        return $this->userHasAccessToRoute($user, 'ventas.index');
    }

    /**
     * Determine si el usuario puede crear una venta
     */
    public function create(User $user): bool
    {
        return $this->userHasAccessToRoute($user, 'ventas.store');
    }

    /**
     * Determine si el usuario puede actualizar la venta
     */
    public function update(User $user, Venta $venta): bool
    {
        // Solo propietario y vendedor pueden modificar ventas
        // Y solo si la venta está pendiente
        return $this->userHasAccessToRoute($user, 'ventas.update') && 
               $venta->estado === 'pendiente';
    }

    /**
     * Determine si el usuario puede eliminar/anular la venta
     */
    public function delete(User $user, Venta $venta): bool
    {
        // Solo propietario puede anular ventas
        return $this->userHasAccessToRoute($user, 'ventas.destroy') &&
               $venta->estado !== 'anulado';
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
