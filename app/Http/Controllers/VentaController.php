<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Producto;
use App\Models\Carrito;
use App\Models\KardexInventario;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VentaController extends Controller
{
    public function __construct(protected CreditService $creditService)
    {
    }

    /**
     * Mostrar listado de ventas
     */
    public function index(Request $request)
    {
        $query = Venta::with([
            'user', 
            'vendedor', 
            'metodoPago', 
            'detalles.producto',
            'credito.cuotas.pagos.metodoPago'
        ])
            ->where('estado', 'pagado') // Solo ventas confirmadas
            ->orderBy('created_at', 'desc');

        // Si es cliente, solo mostrar sus propias ventas
        if ($request->user()->role_id === 3) {
            $query->where('user_id', $request->user()->id);
        }

        $ventas = $query->paginate(20);

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
        ]);
    }

    /**
     * Mostrar detalle de una venta
     */
    public function show($id)
    {
        $venta = Venta::with([
            'user',
            'vendedor',
            'metodoPago',
            'detalles.producto.categoria',
            'credito.cuotas.pagos.metodoPago'
        ])->findOrFail($id);

        return Inertia::render('Ventas/Show', [
            'venta' => $venta,
            'qrData' => [
                'uuid' => $venta->numero_venta
            ]
        ]);
    }

    public function storeVentaContado(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'cliente_id' => 'nullable|exists:users,id',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia'
        ]);

        try {
            DB::beginTransaction();

            $total = 0;
            $ventaItems = [];

            // Calcular total y preparar items
            foreach ($request->items as $item) {
                $producto = Producto::with(['promociones' => function ($q) {
                    $q->where('fecha_inicio', '<=', now())
                      ->where('fecha_fin', '>=', now())
                      ->where('activa', true);
                }])->findOrFail($item['producto_id']);

                // Verificar stock
                if ($producto->stock < $item['cantidad']) {
                    throw new \Exception("Stock insuficiente para {$producto->nombre}");
                }

                // Calcular precio con descuento
                $descuentoMaximo = $producto->promociones->max('descuento') ?? 0;
                $precioFinal = $producto->precio - ($producto->precio * $descuentoMaximo / 100);
                $subtotal = $precioFinal * $item['cantidad'];

                $ventaItems[] = [
                    'producto' => $producto,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'descuento' => $descuentoMaximo,
                    'precio_final' => $precioFinal,
                    'subtotal' => $subtotal
                ];

                $total += $subtotal;
            }

            // Crear venta
            $numeroVenta = $this->generarNumeroVenta();
            
            $venta = Venta::create([
                'numero_venta' => $numeroVenta,
                'user_id' => $request->cliente_id ?? auth()->id(),
                'vendedor_id' => auth()->user()->esVendedor() ? auth()->id() : null,
                'total' => $total,
                'metodo_pago' => $request->metodo_pago,
                'estado' => 'completada'
            ]);

            // Crear detalles y actualizar stock
            foreach ($ventaItems as $item) {
                VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $item['producto']->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento' => $item['descuento'],
                    'subtotal' => $item['subtotal']
                ]);

                // Reducir stock
                $item['producto']->decrement('stock', $item['cantidad']);

                // Registrar en kardex
                KardexInventario::create([
                    'producto_id' => $item['producto']->id,
                    'tipo' => 'salida',
                    'cantidad' => $item['cantidad'],
                    'referencia' => "Venta {$numeroVenta}",
                    'observaciones' => "Venta al contado - {$request->metodo_pago}"
                ]);
            }

            // Limpiar carrito si es cliente autenticado
            if (auth()->check()) {
                $carrito = Carrito::where('user_id', auth()->id())->first();
                if ($carrito) {
                    $carrito->detalles()->delete();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Venta realizada exitosamente',
                'venta_id' => $venta->id,
                'total' => $total
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function storeVentaCredito(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'cliente_id' => 'required|exists:users,id',
            'cuotas' => 'required|integer|min:2|max:12',
            'fecha_inicio' => 'required|date|after_or_equal:today'
        ]);

        try {
            DB::beginTransaction();

            $total = 0;
            $ventaItems = [];

            // Calcular total
            foreach ($request->items as $item) {
                $producto = Producto::with(['promociones' => function ($q) {
                    $q->where('fecha_inicio', '<=', now())
                      ->where('fecha_fin', '>=', now())
                      ->where('activa', true);
                }])->findOrFail($item['producto_id']);

                if ($producto->stock < $item['cantidad']) {
                    throw new \Exception("Stock insuficiente para {$producto->nombre}");
                }

                $descuentoMaximo = $producto->promociones->max('descuento') ?? 0;
                $precioFinal = $producto->precio - ($producto->precio * $descuentoMaximo / 100);
                $subtotal = $precioFinal * $item['cantidad'];

                $ventaItems[] = [
                    'producto' => $producto,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'descuento' => $descuentoMaximo,
                    'precio_final' => $precioFinal,
                    'subtotal' => $subtotal
                ];

                $total += $subtotal;
            }

            // Crear venta primero
            $numeroVenta = $this->generarNumeroVenta();
            
            $venta = Venta::create([
                'numero_venta' => $numeroVenta,
                'user_id' => $request->cliente_id,
                'vendedor_id' => auth()->id(),
                'total' => $total,
                'metodo_pago' => 'credito',
                'estado' => 'pendiente'
            ]);

            // Crear detalles y reducir stock
            foreach ($ventaItems as $item) {
                VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $item['producto']->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento' => $item['descuento'],
                    'subtotal' => $item['subtotal']
                ]);

                $item['producto']->decrement('stock', $item['cantidad']);

                // Registrar en kardex
                KardexInventario::create([
                    'producto_id' => $item['producto']->id,
                    'tipo' => 'salida',
                    'cantidad' => $item['cantidad'],
                    'referencia' => "Venta {$numeroVenta}",
                    'observaciones' => "Venta a crédito - {$request->cuotas} cuotas"
                ]);
            }

            // Crear crédito usando CreditService
            $credito = $this->creditService->createCredit(
                $request->cliente_id,
                $venta->id,
                $total,
                $request->cuotas,
                $request->fecha_inicio
            );

            DB::commit();

            return response()->json([
                'message' => 'Venta a crédito creada exitosamente',
                'venta_id' => $venta->id,
                'credito_id' => $credito->id,
                'total' => $total,
                'cuotas' => $request->cuotas
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    private function generarNumeroVenta()
    {
        $fecha = now()->format('Ymd');
        $ultimaVenta = Venta::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $secuencia = $ultimaVenta ? intval(substr($ultimaVenta->numero_venta, -4)) + 1 : 1;
        
        return sprintf('VE-%s-%04d', $fecha, $secuencia);
    }
}
