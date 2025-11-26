<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Promocion;
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
            ]);
        }

        // Búsqueda de productos
        $productosQuery = Producto::with(['categoria', 'imagenes'])
            ->where(function($q) use ($query) {
                $q->where('nombre', 'ILIKE', "%{$query}%")
                  ->orWhere('codigo', 'ILIKE', "%{$query}%")
                  ->orWhereHas('categoria', function($catQ) use ($query) {
                      $catQ->where('nombre', 'ILIKE', "%{$query}%");
                  });
            });

        // Si es cliente, solo productos activos/publicados
        if ($user->esCliente()) {
            $productosQuery->where('activo', true)
                          ->where('stock', '>', 0);
        }

        $productos = $productosQuery->limit(10)->get()->map(function($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo' => $producto->codigo,
                'precio' => $producto->precio_venta,
                'stock' => $producto->stock,
                'categoria' => $producto->categoria->nombre ?? 'N/A',
                'imagen' => $producto->imagenes->first()->ruta ?? null,
            ];
        });

        // Búsqueda de promociones
        $promocionesQuery = Promocion::where('activo', true)
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
                'descuento_porcentaje' => $promocion->descuento_porcentaje,
                'descuento_monto' => $promocion->descuento_monto,
                'fecha_inicio' => $promocion->fecha_inicio,
                'fecha_fin' => $promocion->fecha_fin,
            ];
        });

        return response()->json([
            'productos' => $productos,
            'promociones' => $promociones,
        ]);
    }
}
