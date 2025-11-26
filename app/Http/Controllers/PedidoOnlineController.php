<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Carrito;
use App\Models\Producto;
use App\Models\MetodoPago;
use App\Models\KardexInventario;
use App\Services\PagoFacilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

/**
 * Controlador para pedidos online realizados por clientes
 * NO afecta la funcionalidad de ventas en tienda física
 */
class PedidoOnlineController extends Controller
{
    public function __construct(
        protected PagoFacilService $pagoFacilService
    ) {}

    /**
     * Crear pedido desde el carrito (SOLO para clientes online)
     */
    public function realizarPedido(Request $request)
    {
        $request->validate([
            'direccion_entrega' => 'required|string|min:10|max:500'
        ]);

        try {
            $user = auth()->user();

            // Obtener carrito del usuario
            $carrito = Carrito::with('detalles.producto')->where('user_id', $user->id)->first();

            if (!$carrito || $carrito->detalles->count() === 0) {
                return back()->with('error', 'Tu carrito está vacío');
            }

            DB::beginTransaction();

            $total = 0;
            $ventaItems = [];

            // Calcular total y preparar items
            foreach ($carrito->detalles as $detalle) {
                $producto = $detalle->producto;

                // Verificar stock
                if ($producto->stock_actual < $detalle->cantidad) {
                    throw new \Exception("Stock insuficiente para {$producto->nombre}");
                }

                // Calcular precio con descuento
                $descuentoMaximo = $producto->promociones()
                    ->where('estado', true)
                    ->where('fecha_inicio', '<=', now())
                    ->where('fecha_fin', '>=', now())
                    ->max('valor_descuento_decimal') ?? 0;

                $precioFinal = $producto->precio_venta - ($producto->precio_venta * $descuentoMaximo / 100);
                $subtotal = $precioFinal * $detalle->cantidad;

                $ventaItems[] = [
                    'producto' => $producto,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $producto->precio_venta,
                    'descuento' => $descuentoMaximo,
                    'precio_final' => $precioFinal,
                    'subtotal' => $subtotal
                ];

                $total += $subtotal;
            }

            // Obtener método de pago QR
            $metodoPagoQR = MetodoPago::where('nombre', 'QR')->first();
            if (!$metodoPagoQR) {
                throw new \Exception('Método de pago QR no configurado');
            }

            // Crear venta (pedido online)
            $numeroVenta = $this->generarNumeroVenta();
            
            $venta = Venta::create([
                'numero_venta' => $numeroVenta,
                'user_id' => $user->id,
                'vendedor_id' => $user->id,
                'total' => $total,
                'metodo_pago_id' => $metodoPagoQR->id,
                'tipo_pago' => 'contado', // Siempre contado para online
                'estado' => 'pendiente', // Pendiente hasta que pague
                'origen' => 'online',
                'direccion_entrega' => $request->direccion_entrega
            ]);

            // Crear detalles de la venta
            foreach ($ventaItems as $item) {
                VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $item['producto']->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento' => $item['descuento'],
                    'subtotal' => $item['subtotal']
                ]);

                // NO reducir stock aún (se reduce cuando se confirme el pago)
            }

            // Generar QR de PagoFácil (simulado)
            $glosa = "Pedido Online #{$numeroVenta}";
            $qrData = $this->pagoFacilService->generarQRVentaSimulado(
                $venta->id,
                $total,
                $glosa
            );

            // Actualizar venta con datos del QR
            $venta->update([
                'pago_facil_transaction_id' => $qrData['transaction_id'],
                'pago_facil_qr_image' => $qrData['qr_image'],
                'pago_facil_status' => 'pending'
            ]);

            // Vaciar el carrito
            $carrito->detalles()->delete();

            DB::commit();

            Log::info('Pedido online creado exitosamente', [
                'venta_id' => $venta->id,
                'user_id' => $user->id,
                'total' => $total
            ]);

            return redirect()->route('mis-pedidos.show', $venta->id)
                ->with('success', '¡Pedido creado exitosamente! Escanea el código QR para pagar.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear pedido online', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Error al crear el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Webhook simulado para confirmar pago de venta online
     */
    public function webhookVentaSimulado(Request $request)
    {
        try {
            // Validar webhook
            if (!$this->pagoFacilService->validarWebhookSimulado($request->all())) {
                return response()->json(['error' => 'Webhook inválido'], 400);
            }

            $transactionId = $request->input('transaction_id');
            $status = $request->input('status');

            // Buscar la venta por transaction_id
            $venta = Venta::where('pago_facil_transaction_id', $transactionId)->first();

            if (!$venta) {
                Log::warning('Venta no encontrada para transaction_id', ['transaction_id' => $transactionId]);
                return response()->json(['error' => 'Venta no encontrada'], 404);
            }

            // Si ya está pagada, no procesar de nuevo
            if ($venta->estado === 'pagado') {
                return response()->json(['message' => 'Venta ya procesada'], 200);
            }

            DB::beginTransaction();

            if ($status === 'completed') {
                // Actualizar estado del pago
                $venta->update([
                    'pago_facil_status' => 'completed',
                    'estado' => 'pagado' // Pasa a pagado, pero no a "enviado" aún
                ]);

                // AHORA SÍ reducir stock y registrar en kardex
                foreach ($venta->detalles as $detalle) {
                    $producto = $detalle->producto;
                    $producto->decrement('stock_actual', $detalle->cantidad);

                    // Registrar en kardex
                    KardexInventario::create([
                        'producto_id' => $producto->id,
                        'tipo' => 'salida',
                        'cantidad' => $detalle->cantidad,
                        'referencia' => "Venta Online {$venta->numero_venta}",
                        'observaciones' => "Pedido online pagado con QR"
                    ]);
                }

                DB::commit();

                Log::info('Pago de venta online confirmado via webhook simulado', [
                    'venta_id' => $venta->id,
                    'transaction_id' => $transactionId
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Pago confirmado exitosamente',
                    'venta_id' => $venta->id
                ]);

            } else {
                // Pago fallido
                $venta->update([
                    'pago_facil_status' => 'failed'
                ]);

                DB::commit();

                return response()->json([
                    'success' => false,
                    'message' => 'Pago fallido'
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en webhook de venta simulado', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            
            return response()->json([
                'error' => 'Error al procesar webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint de pruebas para simular confirmación de pago
     * SOLO PARA DESARROLLO - Eliminar en producción
     */
    public function confirmarPagoSimulado(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|string'
        ]);

        $transactionId = $request->input('transaction_id');

        // Simular webhook de confirmación
        $webhookData = [
            'transaction_id' => $transactionId,
            'status' => 'completed',
            'fecha_pago' => now()->toIso8601String(),
            'simulated' => true
        ];

        // Llamar al webhook simulado
        $webhookRequest = Request::create('/webhook/pagofacil-simulado/venta', 'POST', $webhookData);
        return $this->webhookVentaSimulado($webhookRequest);
    }

    /**
     * Marcar pedido como enviado (solo vendedor/admin)
     * Esto mueve el pedido de "pagado" a "enviado"
     */
    public function marcarComoEnviado($ventaId)
    {
        try {
            $venta = Venta::findOrFail($ventaId);

            // Validar que sea un pedido online
            if ($venta->origen !== 'online') {
                return redirect()->route('pedidos.index', ['origen' => 'online', 'estado' => 'pagado'])
                    ->with('error', 'Solo se pueden marcar como enviados los pedidos online');
            }

            // Validar que esté pagado
            if ($venta->estado !== 'pagado') {
                return redirect()->route('pedidos.index', ['origen' => 'online', 'estado' => 'pagado'])
                    ->with('error', 'El pedido debe estar pagado para marcarlo como enviado');
            }

            $venta->update(['estado' => 'enviado']);

            Log::info('Pedido marcado como enviado', ['venta_id' => $ventaId]);

            return redirect()->route('pedidos.index', ['origen' => 'online', 'estado' => 'enviado'])
                ->with('success', 'Pedido marcado como enviado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error al marcar pedido como enviado', [
                'venta_id' => $ventaId,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('pedidos.index', ['origen' => 'online'])
                ->with('error', 'Error al marcar como enviado: ' . $e->getMessage());
        }
    }

    /**
     * Generar número de venta
     */
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
