<?php

namespace App\Services;

use App\Models\Venta;
use Illuminate\Support\Carbon;

class VentaService
{
    /**
     * Generar número único de venta
     */
    public function generarNumeroVenta(): string
    {
        $fecha = Carbon::now()->format('Ymd');
        $ultimaVenta = Venta::whereDate('created_at', Carbon::today())
            ->orderBy('id', 'desc')
            ->first();

        $secuencial = $ultimaVenta ? intval(substr($ultimaVenta->numero_venta, -4)) + 1 : 1;

        return 'V-' . $fecha . '-' . str_pad($secuencial, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calcular totales de la venta
     */
    public function calcularTotales(array $detalles): array
    {
        $subtotal = 0;
        $descuentoTotal = 0;

        foreach ($detalles as $detalle) {
            $subtotal += $detalle['precio_unitario'] * $detalle['cantidad'];
            $descuentoTotal += $detalle['descuento'] * $detalle['cantidad'];
        }

        $total = $subtotal - $descuentoTotal;

        return [
            'subtotal' => round($subtotal, 2),
            'descuento' => round($descuentoTotal, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Verificar disponibilidad de stock para venta
     */
    public function verificarStock(array $detalles): array
    {
        $errores = [];

        foreach ($detalles as $detalle) {
            $producto = \App\Models\Producto::find($detalle['producto_id']);
            
            if (!$producto) {
                $errores[] = "Producto con ID {$detalle['producto_id']} no encontrado";
                continue;
            }

            if ($producto->stock < $detalle['cantidad']) {
                $errores[] = "Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}, Solicitado: {$detalle['cantidad']}";
            }
        }

        return $errores;
    }

    /**
     * Descontar stock de productos vendidos
     */
    public function descontarStock(array $detalles): void
    {
        foreach ($detalles as $detalle) {
            $producto = \App\Models\Producto::find($detalle['producto_id']);
            
            if ($producto) {
                $producto->decrement('stock', $detalle['cantidad']);
            }
        }
    }

    /**
     * Restaurar stock (al anular venta)
     */
    public function restaurarStock(Venta $venta): void
    {
        foreach ($venta->detalles as $detalle) {
            $producto = $detalle->producto;
            
            if ($producto) {
                $producto->increment('stock', $detalle->cantidad);
            }
        }
    }
}
