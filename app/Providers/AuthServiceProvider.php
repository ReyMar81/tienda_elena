<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Producto::class => \App\Policies\ProductoPolicy::class,
        \App\Models\Categoria::class => \App\Policies\CategoriaPolicy::class,
        \App\Models\Promocion::class => \App\Policies\PromocionPolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Carrito::class => \App\Policies\CarritoPolicy::class,
        \App\Models\Venta::class => \App\Policies\VentaPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
