<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? array_merge(
                    $request->user()->toArray(),
                    [
                        'roles' => $request->user()->role ? [$request->user()->role->nombre] : [],
                        'profile_photo_url' => $request->user()->profile_photo_url ?? null,
                    ]
                ) : null,
                // Compartir permisos dinámicos para vistas Vue
                'permissions' => function () use ($request) {
                    if (!$request->user()) {
                        return [];
                    }
                    
                    $user = $request->user();
                    $roleId = $user->role_id;
                    
                    // Helper para verificar acceso a ruta
                    $hasAccess = function($route) use ($roleId, $user) {
                        if (!$roleId) {
                            return false;
                        }
                        
                        return \App\Models\MenuItem::where('role_id', $roleId)
                            ->where('ruta_laravel', $route)
                            ->exists();
                    };
                    
                    return [
                        'productos' => [
                            'viewAny' => $user->can('viewAny', \App\Models\Producto::class),
                            'create' => $user->can('create', \App\Models\Producto::class),
                            'update' => $hasAccess('productos.edit'),
                            'delete' => $hasAccess('productos.destroy'),
                        ],
                        'categorias' => [
                            'viewAny' => $user->can('viewAny', \App\Models\Categoria::class),
                            'create' => $user->can('create', \App\Models\Categoria::class),
                            'update' => $hasAccess('categorias.edit'),
                            'delete' => $hasAccess('categorias.destroy'),
                        ],
                        'promociones' => [
                            'viewAny' => $user->can('viewAny', \App\Models\Promocion::class),
                            'create' => $user->can('create', \App\Models\Promocion::class),
                            'update' => $hasAccess('promociones.edit'),
                            'delete' => $hasAccess('promociones.destroy'),
                        ],
                    ];
                },
            ],
            'menuItems' => function () use ($request) {
                if (!$request->user()) {
                    return [];
                }
                return app(\App\Services\MenuService::class)->getMenuForUser($request->user());
            },
            // Flash messages para notificaciones
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            // Contador de visitas de página
            'pageVisits' => function () use ($request) {
                $ruta = $request->path();
                $visitaActual = \App\Models\PageVisit::where('ruta', $ruta)->first();
                $totalVisitas = \App\Models\PageVisit::sum('contador');
                
                return [
                    'current' => $visitaActual ? $visitaActual->contador : 0,
                    'total' => $totalVisitas,
                    'route' => $ruta,
                ];
            },
        ]);
    }
}
