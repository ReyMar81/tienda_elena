<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Promocion;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $user = auth()->user();
        
        if (strlen($query) < 2) {
            return response()->json([
                'productos' => [],
                'promociones' => [],
                'menus' => [],
                'query' => $query,
                'count' => 0,
            ]);
        }

        // Búsqueda de productos
        $productosQuery = Producto::with(['categoria', 'imagenes'])
            ->where(function($q) use ($query) {
                $q->where('nombre', 'ILIKE', "%{$query}%")
                  ->orWhere('codigo', 'ILIKE', "%{$query}%")
                  ->orWhere('marca', 'ILIKE', "%{$query}%")
                  ->orWhereHas('categoria', function($catQ) use ($query) {
                      $catQ->where('nombre', 'ILIKE', "%{$query}%");
                  });
            });

        // Si es cliente, solo productos activos con stock
        if ($user && $user->esCliente()) {
            $productosQuery->where('estado', true)
                          ->where('stock_actual', '>', 0);
        }

        $productos = $productosQuery->limit(10)->get()->map(function($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo' => $producto->codigo,
                'marca' => $producto->marca,
                'precio' => $producto->precio_venta,
                'stock' => $producto->stock_actual,
                'activo' => $producto->estado,
                'categoria' => $producto->categoria->nombre ?? 'N/A',
                'categoria_id' => $producto->categoria_id,
                'imagen' => $producto->imagenes->first()->ruta ?? null,
            ];
        });

        // Búsqueda de promociones activas
        $promocionesQuery = Promocion::where('estado', true)
            ->where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now())
            ->where(function($q) use ($query) {
                $q->where('nombre', 'ILIKE', "%{$query}%")
                  ->orWhere('descripcion', 'ILIKE', "%{$query}%");
            });

        $promociones = $promocionesQuery->limit(10)->get()->map(function($promocion) {
            return [
                'id' => $promocion->id,
                'nombre' => $promocion->nombre,
                'descripcion' => $promocion->descripcion,
                'descuento_porcentaje' => $promocion->valor_descuento_decimal,
                'descuento_monto' => null,
                'fecha_inicio' => $promocion->fecha_inicio->format('Y-m-d'),
                'fecha_fin' => $promocion->fecha_fin->format('Y-m-d'),
            ];
        });

        $totalResults = $productos->count() + $promociones->count();

        // Búsqueda de menús/rutas disponibles para el usuario
        // Menús: ajustamos a la estructura real de la tabla `menu_items`
        $menusQuery = MenuItem::whereNull('parent_id')
            ->with('children')
            ->where(function($q) use ($query) {
                $q->where('etiqueta', 'ILIKE', "%{$query}%")
                  ->orWhere('ruta_laravel', 'ILIKE', "%{$query}%");
            });

        // Filtrar por rol del usuario (si hay usuario, mostrar ítems asignados a su rol)
        if ($user) {
            $menusQuery->where(function($q) use ($user) {
                $q->where('role_id', $user->role_id);
            });
        } else {
            // Sin usuario logueado, no mostramos elementos privados
            $menusQuery->whereRaw('1 = 0');
        }

        $menus = $menusQuery->limit(8)->get()->map(function($menu) {
            $submenus = $menu->children->map(function($child) {
                return [
                    'id' => $child->id,
                    'label' => $child->etiqueta,
                    'route' => $child->ruta_laravel,
                    'icon' => $child->icono,
                ];
            })->values();

            return [
                'id' => $menu->id,
                'label' => $menu->etiqueta,
                'route' => $menu->ruta_laravel,
                'icon' => $menu->icono,
                'submenus' => $submenus,
            ];
        });

        // También buscar en submenús (usar columnas reales de `menu_items`)
        $submenuQuery = MenuItem::whereNotNull('parent_id')
            ->where(function($q) use ($query) {
                $q->where('etiqueta', 'ILIKE', "%{$query}%")
                  ->orWhere('ruta_laravel', 'ILIKE', "%{$query}%");
            });

        if ($user) {
            $submenuQuery->where('role_id', $user->role_id);
        } else {
            $submenuQuery->whereRaw('1 = 0');
        }

        $submenus = $submenuQuery->limit(8)->get()->map(function($submenu) {
            return [
                'id' => $submenu->id,
                'label' => $submenu->etiqueta,
                'route' => $submenu->ruta_laravel,
                'icon' => $submenu->icono,
                'parent' => $submenu->parent->etiqueta ?? null,
            ];
        });

        // Combinar menús y submenús
        $allMenus = $menus->merge($submenus)->unique('id')->take(8);
        $totalResults += $allMenus->count();

        return response()->json([
            'productos' => $productos,
            'promociones' => $promociones,
            'menus' => $allMenus->values(),
            'query' => $query,
            'count' => $totalResults,
        ]);
    }
}
