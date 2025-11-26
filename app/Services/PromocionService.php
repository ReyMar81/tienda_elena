<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Support\Carbon;

class PromocionService
{
    /**
     * Calcular el descuento aplicable a un producto
     */
    public function calcularDescuentoProducto(Producto $producto, string $tipoCliente = 'minorista'): float
    {
        $descuentoMaximo = 0;
        
        // Obtener promociones activas
        $promocionesActivas = Promocion::where('estado', true)
            ->where('fecha_inicio', '<=', Carbon::now())
            ->where('fecha_fin', '>=', Carbon::now())
            ->get();

        foreach ($promocionesActivas as $promocion) {
            // Verificar si aplica a este producto directamente
            $aplicaDirecto = $promocion->productos()
                ->wherePivot('producto_id', $producto->id)
                ->wherePivot($tipoCliente === 'mayorista' ? 'aplica_mayorista' : 'aplica_minorista', true)
                ->exists();

            if ($aplicaDirecto) {
                $descuentoMaximo = max($descuentoMaximo, $promocion->valor_descuento_decimal);
                continue;
            }

            // Verificar si aplica por categoría
            if ($producto->categoria_id) {
                $aplicaCategoria = $promocion->categorias()
                    ->wherePivot('categoria_id', $producto->categoria_id)
                    ->wherePivot($tipoCliente === 'mayorista' ? 'aplica_mayorista' : 'aplica_minorista', true)
                    ->exists();

                if ($aplicaCategoria) {
                    $descuentoMaximo = max($descuentoMaximo, $promocion->valor_descuento_decimal);
                }
            }
        }

        return $descuentoMaximo;
    }

    /**
     * Calcular precio final con descuento
     */
    public function calcularPrecioFinal(Producto $producto, float $precio, string $tipoCliente = 'minorista'): array
    {
        $descuento = $this->calcularDescuentoProducto($producto, $tipoCliente);
        $montoDescuento = ($precio * $descuento) / 100;
        $precioFinal = $precio - $montoDescuento;

        return [
            'precio_original' => $precio,
            'descuento_porcentaje' => $descuento,
            'descuento_monto' => round($montoDescuento, 2),
            'precio_final' => round($precioFinal, 2),
        ];
    }

    /**
     * Aplicar descuentos a múltiples productos (carrito)
     */
    public function aplicarDescuentosCarrito(array $items, string $tipoCliente = 'minorista'): array
    {
        $resultado = [];

        foreach ($items as $item) {
            $producto = Producto::find($item['producto_id']);
            
            if (!$producto) {
                continue;
            }

            $precio = $item['precio_unitario'] ?? $producto->precio_venta;
            $precioInfo = $this->calcularPrecioFinal($producto, $precio, $tipoCliente);

            $resultado[] = [
                'producto_id' => $producto->id,
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $precio,
                'descuento_porcentaje' => $precioInfo['descuento_porcentaje'],
                'descuento_monto' => $precioInfo['descuento_monto'],
                'subtotal' => round($precioInfo['precio_final'] * $item['cantidad'], 2),
            ];
        }

        return $resultado;
    }
}
