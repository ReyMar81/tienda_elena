<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCarritoRequest;
use App\Http\Requests\UpdateCarritoRequest;
use App\Models\Carrito;
use App\Models\CarritoDetalle;
use App\Models\Producto;
use App\Services\PromocionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CarritoController extends Controller
{
    protected $promocionService;

    public function __construct(PromocionService $promocionService)
    {
        $this->promocionService = $promocionService;
    }

    /**
     * Mostrar el carrito del usuario
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Carrito::class);
        $carrito = Carrito::with(['detalles.producto.categoria', 'detalles.producto.imagenes'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$carrito) {
            return Inertia::render('Carrito/Index', [
                'carrito' => null,
                'detalles' => [],
                'total' => 0,
            ]);
        }

        // Aplicar descuentos automáticos
        $detallesConDescuento = $carrito->detalles->map(function ($detalle) {
            $descuento = $this->promocionService->calcularDescuentoProducto(
                $detalle->producto,
                'minorista'
            );

            $precioUnitario = $detalle->producto->precio_venta;
            $montoDescuento = ($precioUnitario * $descuento) / 100;

            return [
                'id' => $detalle->id,
                'producto' => $detalle->producto,
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $precioUnitario,
                'descuento_porcentaje' => $descuento,
                'descuento_monto' => round($montoDescuento, 2),
                'precio_con_descuento' => round($precioUnitario - $montoDescuento, 2),
                'subtotal' => round(($precioUnitario - $montoDescuento) * $detalle->cantidad, 2),
            ];
        });

        $total = $detallesConDescuento->sum('subtotal');

        return Inertia::render('Carrito/Index', [
            'carrito' => $carrito,
            'detalles' => $detallesConDescuento,
            'total' => round($total, 2),
        ]);
    }

    /**
     * Agregar producto al carrito
     */
    public function store(AddToCarritoRequest $request)
    {
        $this->authorize('create', Carrito::class);
        

        $producto = Producto::findOrFail($request->producto_id);

        // Verificar stock
        if ($producto->stock_actual < $request->cantidad) {
            return back()->with('error', 'Stock insuficiente');
        }

        // Obtener o crear carrito
        $carrito = Carrito::firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        // Verificar si el producto ya existe en el carrito
        $detalleExistente = CarritoDetalle::where('carrito_id', $carrito->id)
            ->where('producto_id', $producto->id)
            ->first();

        // Calcular descuento del producto
        $descuentoPorcentaje = $this->promocionService->calcularDescuentoProducto($producto, 'minorista');
        $montoDescuento = ($producto->precio_venta * $descuentoPorcentaje) / 100;

        if ($detalleExistente) {
            // Actualizar cantidad y descuento
            $nuevaCantidad = $detalleExistente->cantidad + $request->cantidad;

            if ($producto->stock_actual < $nuevaCantidad) {
                return back()->with('error', 'Stock insuficiente para la cantidad solicitada');
            }

            $detalleExistente->update([
                'cantidad' => $nuevaCantidad,
                'precio_unitario' => $producto->precio_venta,
                'descuento' => round($montoDescuento, 2),
            ]);

            return back()->with('success', 'Cantidad actualizada en el carrito');
        }

        // Crear nuevo detalle con descuento calculado
        CarritoDetalle::create([
            'carrito_id' => $carrito->id,
            'producto_id' => $producto->id,
            'cantidad' => $request->cantidad,
            'precio_unitario' => $producto->precio_venta,
            'descuento' => round($montoDescuento, 2),
        ]);

        return back()->with('success', 'Producto agregado al carrito');
    }

    /**
     * Actualizar cantidad de un item del carrito
     */
    public function update(UpdateCarritoRequest $request, CarritoDetalle $carritoDetalle)
    {
        // Verificar que el detalle pertenece al carrito del usuario
        if ($carritoDetalle->carrito->user_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }


        // Verificar stock
        if ($carritoDetalle->producto->stock_actual < $request->cantidad) {
            return back()->with('error', 'Stock insuficiente');
        }

        // Recalcular descuento
        $descuentoPorcentaje = $this->promocionService->calcularDescuentoProducto($carritoDetalle->producto, 'minorista');
        $montoDescuento = ($carritoDetalle->producto->precio_venta * $descuentoPorcentaje) / 100;

        $carritoDetalle->update([
            'cantidad' => $request->cantidad,
            'precio_unitario' => $carritoDetalle->producto->precio_venta,
            'descuento' => round($montoDescuento, 2),
        ]);

        return back()->with('success', 'Cantidad actualizada');
    }

    /**
     * Eliminar un item del carrito
     */
    public function destroy(Request $request, CarritoDetalle $carritoDetalle)
    {
        // Verificar que el detalle pertenece al carrito del usuario
        if ($carritoDetalle->carrito->user_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        $carritoDetalle->delete();

        // Si el carrito quedó vacío, eliminarlo
        $carrito = Carrito::with('detalles')
            ->where('user_id', $request->user()->id)
            ->first();

        if ($carrito && $carrito->detalles->isEmpty()) {
            $carrito->delete();
        }

        return back()->with('success', 'Producto eliminado del carrito');
    }

    /**
     * Vaciar el carrito completamente
     */
    public function vaciar(Request $request)
    {
        $carrito = Carrito::where('user_id', $request->user()->id)->first();

        if ($carrito) {
            $carrito->detalles()->delete();
            $carrito->delete();
        }

        return back()->with('success', 'Carrito vaciado');
    }
}
