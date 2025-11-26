<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoDetalle;
use App\Models\Producto;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function get()
    {
        if (!auth()->check()) {
            return response()->json(['items' => [], 'total' => 0]);
        }

        $carrito = Carrito::with(['detalles.producto.promociones' => function ($q) {
            $q->where('fecha_inicio', '<=', now())
              ->where('fecha_fin', '>=', now())
              ->where('activa', true);
        }])->firstOrCreate(['user_id' => auth()->id()]);

        // Recalcular precios con promociones actuales
        $carrito->detalles->each(function ($detalle) {
            $producto = $detalle->producto;
            $descuentoMaximo = $producto->promociones->max('descuento') ?? 0;
            
            $detalle->precio_unitario = $producto->precio;
            $detalle->descuento = $producto->precio * ($descuentoMaximo / 100);
            $detalle->save();
        });

        return response()->json([
            'items' => $carrito->detalles->map(function ($detalle) {
                return [
                    'id' => $detalle->id,
                    'producto_id' => $detalle->producto_id,
                    'nombre' => $detalle->producto->nombre,
                    'imagen' => $detalle->producto->imagen,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'descuento' => $detalle->descuento,
                    'precio_final' => $detalle->precio_unitario - $detalle->descuento,
                    'subtotal' => $detalle->subtotal()
                ];
            }),
            'total' => $carrito->total()
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1'
        ]);

        $producto = Producto::with(['promociones' => function ($q) {
            $q->where('fecha_inicio', '<=', now())
              ->where('fecha_fin', '>=', now())
              ->where('activa', true);
        }])->findOrFail($request->producto_id);

        // Verificar stock
        if ($producto->stock < $request->cantidad) {
            return response()->json(['error' => 'Stock insuficiente'], 422);
        }

        if (!auth()->check()) {
            return response()->json(['message' => 'Usa localStorage en el frontend'], 200);
        }

        $carrito = Carrito::firstOrCreate(['user_id' => auth()->id()]);

        $descuentoMaximo = $producto->promociones->max('descuento') ?? 0;
        $descuentoMonto = $producto->precio * ($descuentoMaximo / 100);

        $detalle = CarritoDetalle::where('carrito_id', $carrito->id)
            ->where('producto_id', $producto->id)
            ->first();

        if ($detalle) {
            $detalle->cantidad += $request->cantidad;
            $detalle->save();
        } else {
            CarritoDetalle::create([
                'carrito_id' => $carrito->id,
                'producto_id' => $producto->id,
                'cantidad' => $request->cantidad,
                'precio_unitario' => $producto->precio,
                'descuento' => $descuentoMonto
            ]);
        }

        return $this->get();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1'
        ]);

        if (!auth()->check()) {
            return response()->json(['message' => 'Usa localStorage en el frontend'], 200);
        }

        $detalle = CarritoDetalle::whereHas('carrito', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        // Verificar stock
        if ($detalle->producto->stock < $request->cantidad) {
            return response()->json(['error' => 'Stock insuficiente'], 422);
        }

        $detalle->cantidad = $request->cantidad;
        $detalle->save();

        return $this->get();
    }

    public function remove($id)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Usa localStorage en el frontend'], 200);
        }

        $detalle = CarritoDetalle::whereHas('carrito', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        $detalle->delete();

        return $this->get();
    }

    public function clear()
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Usa localStorage en el frontend'], 200);
        }

        $carrito = Carrito::where('user_id', auth()->id())->first();

        if ($carrito) {
            $carrito->detalles()->delete();
        }

        return response()->json(['items' => [], 'total' => 0]);
    }

    public function sync(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1'
        ]);

        if (!auth()->check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $carrito = Carrito::firstOrCreate(['user_id' => auth()->id()]);

        // Limpiar carrito actual
        $carrito->detalles()->delete();

        // Agregar items desde localStorage
        foreach ($request->items as $item) {
            $producto = Producto::with(['promociones' => function ($q) {
                $q->where('fecha_inicio', '<=', now())
                  ->where('fecha_fin', '>=', now())
                  ->where('activa', true);
            }])->find($item['producto_id']);

            if ($producto && $producto->stock >= $item['cantidad']) {
                $descuentoMaximo = $producto->promociones->max('descuento') ?? 0;
                $descuentoMonto = $producto->precio * ($descuentoMaximo / 100);

                CarritoDetalle::create([
                    'carrito_id' => $carrito->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'descuento' => $descuentoMonto
                ]);
            }
        }

        return $this->get();
    }
}
