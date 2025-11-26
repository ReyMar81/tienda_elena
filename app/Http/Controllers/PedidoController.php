<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Carrito;
use App\Models\Credito;
use App\Models\Cuota;
use App\Models\MetodoPago;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\PromocionService;
use App\Services\VentaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PedidoController extends Controller
{
    protected $promocionService;
    protected $ventaService;

    public function __construct(PromocionService $promocionService, VentaService $ventaService)
    {
        $this->promocionService = $promocionService;
        $this->ventaService = $ventaService;
    }

    /**
     * Listar pedidos con filtro por origen y estado
     */
    public function index(Request $request)
    {
        $origen = $request->input('origen', 'tienda'); // tienda u online
        $estado = $request->input('estado', 'pendiente');

        $query = Venta::with(['user', 'vendedor', 'metodoPago', 'detalles.producto'])
            ->orderBy('created_at', 'desc');

        // Filtrar por origen
        if (in_array($origen, ['tienda', 'online'])) {
            $query->where('origen', $origen);
        }

        // Filtrar por estado si se especifica
        if (in_array($estado, ['pendiente', 'pagado', 'enviado', 'anulado'])) {
            $query->where('estado', $estado);
        }

        // Si es cliente, solo mostrar sus propios pedidos
        if ($request->user()->role_id === 3) {
            $query->where('user_id', $request->user()->id);
        }

        $pedidos = $query->paginate(20);

        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidos,
            'filtro_origen' => $origen,
            'filtro_estado' => $estado,
        ]);
    }

    /**
     * Mostrar página de checkout
     */
    public function checkout(Request $request)
    {
        $carrito = Carrito::with(['detalles.producto'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$carrito || $carrito->detalles->isEmpty()) {
            return redirect()->route('carritos.index')
                ->with('error', 'El carrito está vacío');
        }

        // Calcular totales con descuentos
        $detallesConDescuento = $carrito->detalles->map(function ($detalle) {
            $descuento = $this->promocionService->calcularDescuentoProducto(
                $detalle->producto,
                'minorista'
            );

            $precioUnitario = $detalle->producto->precio_venta;
            $montoDescuento = ($precioUnitario * $descuento) / 100;

            return [
                'producto' => $detalle->producto,
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $precioUnitario,
                'descuento' => round($montoDescuento, 2),
                'subtotal' => round(($precioUnitario - $montoDescuento) * $detalle->cantidad, 2),
            ];
        });

        $total = $detallesConDescuento->sum('subtotal');

        $metodosPago = MetodoPago::where('estado', true)->get();

        return Inertia::render('Pedidos/Checkout', [
            'detalles' => $detallesConDescuento,
            'total' => round($total, 2),
            'metodosPago' => $metodosPago,
        ]);
    }

    /**
     * Procesar la venta
     */
    public function store(StoreVentaRequest $request)
    {
        $carrito = Carrito::with(['detalles.producto'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$carrito || $carrito->detalles->isEmpty()) {
            return redirect()->route('carritos.index')
                ->with('error', 'El carrito está vacío');
        }

        // Preparar detalles con descuentos
        $detalles = $carrito->detalles->map(function ($detalle) {
            $descuento = $this->promocionService->calcularDescuentoProducto(
                $detalle->producto,
                'minorista'
            );

            $precioUnitario = $detalle->producto->precio_venta;
            $montoDescuento = ($precioUnitario * $descuento) / 100;

            return [
                'producto_id' => $detalle->producto_id,
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $precioUnitario,
                'descuento' => round($montoDescuento, 2),
                'subtotal' => round(($precioUnitario - $montoDescuento) * $detalle->cantidad, 2),
            ];
        })->toArray();

        // Verificar stock
        $erroresStock = $this->ventaService->verificarStock($detalles);
        if (!empty($erroresStock)) {
            return back()->withErrors(['stock' => implode(', ', $erroresStock)]);
        }

        try {
            DB::beginTransaction();

            // Calcular totales
            $totales = $this->ventaService->calcularTotales($detalles);

            // Crear venta
            $venta = Venta::create([
                'user_id' => $request->user()->id,
                'vendedor_id' => $request->user()->id, // Por ahora el mismo usuario
                'metodo_pago_id' => $request->metodo_pago_id,
                'numero_venta' => $this->ventaService->generarNumeroVenta(),
                'subtotal' => $totales['subtotal'],
                'descuento' => $totales['descuento'],
                'total' => $totales['total'],
                'estado' => $request->tipo_venta === 'contado' ? 'pagado' : 'pendiente',
            ]);

            // Crear detalles de venta
            foreach ($detalles as $detalle) {
                VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'descuento' => $detalle['descuento'],
                    'subtotal' => $detalle['subtotal'],
                ]);
            }

            // Descontar stock
            $this->ventaService->descontarStock($detalles);

            // Si es crédito, crear registro de crédito y cuotas
            if ($request->tipo_venta === 'credito') {
                $this->crearCredito($venta, $request);
            }

            // Vaciar carrito
            $carrito->detalles()->delete();
            $carrito->delete();

            DB::commit();

            return redirect()->route('pedidos.confirmacion', $venta->id)
                ->with('success', 'Venta procesada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Crear crédito y cuotas
     */
    private function crearCredito(Venta $venta, StoreVentaRequest $request): void
    {
        // Calcular monto total con interés
        $interes = ($venta->total * $request->tasa_interes / 100);
        $montoTotalConInteres = $venta->total + $interes;
        $montoCuota = $montoTotalConInteres / $request->meses_plazo;
        
        // Fechas
        $fechaOtorgamiento = now();
        $fechaPrimerPago = Carbon::parse($request->fecha_primer_pago);
        $fechaVencimientoFinal = $fechaPrimerPago->copy()->addMonths($request->meses_plazo - 1);

        // Crear registro de crédito
        $credito = Credito::create([
            'venta_id' => $venta->id,
            'monto_credito' => $venta->total,
            'interes' => $interes,
            'cuotas_total' => $request->meses_plazo,
            'dias_mora' => 0,
            'monto_pagado' => 0,
            'monto_pendiente' => $montoTotalConInteres,
            'fecha_otorgamiento' => $fechaOtorgamiento,
            'fecha_vencimiento' => $fechaVencimientoFinal,
            'estado' => 'pendiente',
        ]);

        // Crear cuotas
        $fechaPago = $fechaPrimerPago->copy();
        $interesPorCuota = $interes / $request->meses_plazo;

        for ($i = 1; $i <= $request->meses_plazo; $i++) {
            Cuota::create([
                'credito_id' => $credito->id,
                'numero_cuota' => $i,
                'monto' => round($montoCuota, 2),
                'interes_cuota' => round($interesPorCuota, 2),
                'dias_mora' => 0,
                'monto_pagado' => 0,
                'monto_pendiente' => round($montoCuota, 2),
                'fecha_vencimiento' => $fechaPago->copy(),
                'estado' => 'pendiente',
            ]);

            $fechaPago->addMonth();
        }
    }

    /**
     * Mostrar confirmación de venta
     */
    public function confirmacion(Venta $venta)
    {
        $this->authorize('view', $venta);

        $venta->load(['detalles.producto', 'metodoPago', 'credito.cuotas']);

        return Inertia::render('Pedidos/Confirmacion', [
            'venta' => $venta,
        ]);
    }
}
