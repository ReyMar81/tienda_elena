<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MenuService;

class MenuController extends Controller
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * Obtener menú dinámico para el usuario autenticado
     * (Deprecado: ahora se comparte via Inertia middleware)
     */
    public function getMenuItems(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([]);
        }

        $menuItems = $this->menuService->getMenuForUser($user);

        return response()->json($menuItems);
    }
}
