<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Pago;
use App\Models\Cuota;
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
                'pago_facil_payment_number' => $qrData['payment_number'] ?? null,
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
            'simulated' => true
        ];

        // Llamar al webhook simulado
        $webhookRequest = Request::create('/webhook/pagofacil-simulado/venta', 'POST', $webhookData);
        return $this->webhookVentaSimulado($webhookRequest);
    }

    /**
     * Callback real de PagoFácil (tcUrlCallBack)
     */
    public function pagofacilCallback(Request $request)
    {
        Log::info('📬 [PagoFácil] Callback recibido', $request->all());

        $pagofacilTransactionId = $request->input('pagofacilTransactionId');
        $companyTransactionId = $request->input('companyTransactionId');
        $pedidoId = $request->input('PedidoID');
        $estado = strtolower($request->input('Estado', 'pending'));

        // Construir la consulta de forma segura: evitar comparar "id" con valores no numéricos
        $query = Venta::query();

        if ($pagofacilTransactionId) {
            $query->orWhere('pago_facil_transaction_id', $pagofacilTransactionId);
        }

        if ($companyTransactionId) {
            $query->orWhere('pago_facil_payment_number', $companyTransactionId);
        }

        if ($pedidoId) {
            // Buscar por numero_venta siempre
            $query->orWhere('numero_venta', $pedidoId);

            // Muchas integraciones envían el PedidoID como el payment_number (ej. VENTA-24-1764...)
            // Buscar también en la columna 'pago_facil_payment_number' para cubrir ese caso
            $query->orWhere('pago_facil_payment_number', $pedidoId);

            // Sólo comparar con 'id' si el valor es numérico
            if (is_numeric($pedidoId)) {
                $query->orWhere('id', (int) $pedidoId);
            }
        }

        $venta = $query->first();

        if ($venta) {
            Log::info('🔎 [PagoFácil] Venta encontrada para callback', [
                'venta_id' => $venta->id,
                'pagofacilTransactionId' => $pagofacilTransactionId,
                'companyTransactionId' => $companyTransactionId,
                'PedidoID' => $pedidoId,
            ]);
        }

        // Si no es una venta, intentamos localizar un pago (cuota)
        $pago = null;
        if (!$venta) {
            // Buscar Pago por transaction id o payment number o PedidoID
            $pagoQuery = Pago::query();
            if ($pagofacilTransactionId) {
                $pagoQuery->orWhere('pago_facil_transaction_id', $pagofacilTransactionId);
            }
            if ($companyTransactionId) {
                $pagoQuery->orWhere('pago_facil_payment_number', $companyTransactionId);
            }
            if ($pedidoId) {
                $pagoQuery->orWhere('pago_facil_payment_number', $pedidoId);
            }

            $pago = $pagoQuery->first();

            if (!$pago) {
                Log::warning('⚠️ [PagoFácil] Venta/Pago no encontrado para callback', [
                    'pagofacilTransactionId' => $pagofacilTransactionId,
                    'companyTransactionId' => $companyTransactionId,
                    'PedidoID' => $pedidoId,
                ]);

                return response()->json([
                    'error' => 1,
                    'status' => 404,
                    'message' => 'Pedido/Pago no encontrado',
                    'values' => false,
                ], 404);
            }
            else {
                // Log which field likely matched
                $matchedBy = null;
                if ($pagofacilTransactionId && $pago->pago_facil_transaction_id === $pagofacilTransactionId) {
                    $matchedBy = 'transaction_id';
                } elseif ($companyTransactionId && $pago->pago_facil_payment_number === $companyTransactionId) {
                    $matchedBy = 'payment_number';
                } elseif ($pedidoId && $pago->pago_facil_payment_number === $pedidoId) {
                    $matchedBy = 'pedido_id';
                }

                Log::info('🔎 [PagoFácil] Pago encontrado para callback', [
                    'pago_id' => $pago->id,
                    'matched_by' => $matchedBy,
                    'pago_facil_transaction_id' => $pago->pago_facil_transaction_id,
                    'pago_facil_payment_number' => $pago->pago_facil_payment_number,
                    'PedidoID' => $pedidoId,
                ]);
            }
        }

        try {
            $status = $this->mapearEstadoCallback($estado);

            // Si tenemos una venta, procesarla como antes
            if ($venta) {
                if ($status === 'completed' && $venta->estado !== 'pagado') {
                    DB::transaction(function () use ($venta, $request, $pagofacilTransactionId, $companyTransactionId) {
                        $venta->update([
                            'estado' => 'pagado',
                            'pago_facil_status' => 'completed',
                            'pago_facil_transaction_id' => $pagofacilTransactionId ?: $venta->pago_facil_transaction_id,
                            'pago_facil_payment_number' => $companyTransactionId ?: $venta->pago_facil_payment_number,
                            'pago_facil_raw_response' => json_encode($request->all()),
                        ]);

                        foreach ($venta->detalles as $detalle) {
                            $producto = $detalle->producto;
                            if (!$producto) {
                                continue;
                            }

                            $producto->decrement('stock_actual', $detalle->cantidad);

                            KardexInventario::create([
                                'producto_id' => $producto->id,
                                'tipo' => 'salida',
                                'cantidad' => $detalle->cantidad,
                                'referencia' => "Venta Online {$venta->numero_venta}",
                                'observaciones' => 'Pago confirmado vía callback PagoFácil',
                            ]);
                        }
                    });
                } else {
                    $venta->update([
                        'pago_facil_status' => $status,
                        'pago_facil_raw_response' => json_encode($request->all()),
                    ]);
                }
            } else {
                // Procesar pago (cuota)
                if ($status === 'completed' && $pago->pago_facil_status !== 'completed') {
                    DB::transaction(function () use ($pago, $request) {
                        $pago->update([
                            'pago_facil_status' => 'completed',
                            'pago_facil_raw_response' => json_encode($request->all()),
                            'pago_facil_transaction_id' => $request->input('pagofacilTransactionId') ?: $pago->pago_facil_transaction_id,
                            'pago_facil_payment_number' => $request->input('companyTransactionId') ?: $pago->pago_facil_payment_number,
                            'fecha' => $pago->fecha ?? now(),
                        ]);

                        // Actualizar cuota asociada (hacer un update explícito para evitar escribir columnas no existentes)
                        $cuota = $pago->cuota;
                        if ($cuota) {
                            $nuevoMontoPagado = ($cuota->monto_pagado ?? 0) + $pago->monto;
                            $nuevoEstado = $cuota->estado;
                            if ($nuevoMontoPagado >= ($cuota->monto ?? 0)) {
                                $nuevoEstado = 'pagada';
                            }

                            DB::table('cuotas')->where('id', $cuota->id)->update([
                                'monto_pagado' => $nuevoMontoPagado,
                                'estado' => $nuevoEstado,
                                'updated_at' => now(),
                            ]);

                            // Verificar crédito
                            $credito = $cuota->credito;
                            if ($credito) {
                                $todasPagadas = $credito->cuotas()->where('estado', '!=', 'pagado')->count() === 0;
                                if ($todasPagadas) {
                                    $credito->update(['estado' => 'pagado']);
                                }
                            }
                        }
                    });
                } else {
                    $pago->update([
                        'pago_facil_status' => $status,
                        'pago_facil_raw_response' => json_encode($request->all()),
                    ]);
                }
            }

            return response()->json([
                'error' => 0,
                'status' => 1,
                'message' => 'Callback procesado correctamente',
                'values' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [PagoFácil] Error al procesar callback', [
                'venta_id' => isset($venta) && $venta ? $venta->id : null,
                'pago_id' => isset($pago) && $pago ? $pago->id : null,
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'error' => 1,
                'status' => 500,
                'message' => 'Error al procesar callback',
                'values' => false,
            ], 500);
        }
    }

    private function mapearEstadoCallback(string $estado): string
    {
        $trimmed = trim($estado);

        // Si el callback envía un código numérico (p. ej. 2 = pagado)
        if (is_numeric($trimmed)) {
            $code = (int) $trimmed;
            // Mapear códigos numéricos comunes a estados
            $map = [
                0 => 'pending',
                1 => 'pending',
                2 => 'completed', // Pago realizado
                3 => 'cancelled',
                4 => 'expired',
            ];

            return $map[$code] ?? 'pending';
        }

        $normalized = strtolower($trimmed);

        $completed = ['completed', 'complete', 'success', 'successful', 'paid', 'pagado', 'aprobado'];
        $cancelled = ['cancelled', 'canceled', 'rechazado', 'denied', 'failed', 'error', 'rechazado'];
        $expired = ['expired', 'expirado', 'timeout', 'timeout_interrupted'];

        if (in_array($normalized, $completed, true)) {
            return 'completed';
        }

        if (in_array($normalized, $cancelled, true)) {
            return 'cancelled';
        }

        if (in_array($normalized, $expired, true)) {
            return 'expired';
        }

        return 'pending';
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

        // Para evitar condiciones de carrera al generar el número de venta,
        // bloqueamos la tabla ventas dentro de la transacción activa.
        // Esto garantiza que dos procesos concurrentes no reciban la misma secuencia.
        try {
            DB::statement('LOCK TABLE ventas IN EXCLUSIVE MODE');
        } catch (\Exception $e) {
            // Si el motor no soporta el lock o falla, seguimos sin bloqueo (fall-back).
            Log::warning('No se pudo bloquear la tabla ventas al generar número de venta: ' . $e->getMessage());
        }

        // Obtener la máxima secuencia usada hoy. Usar split_part para soportar
        // secuencias de más de 4 dígitos (ej. 10000) y evitar errores cuando
        // los sufijos superan 9999.
        // Evitar errores al castear cadenas vacías: usar NULLIF para convertir '' a NULL
        // y luego castear con ::integer. MAX ignorará los NULLs.
        $maxSeq = DB::table('ventas')
            ->whereDate('created_at', today())
            ->selectRaw("MAX(NULLIF(split_part(numero_venta, '-', 3), '')::INTEGER) as max_seq")
            ->value('max_seq');

        $secuencia = ($maxSeq ? (int) $maxSeq : 0) + 1;

        return sprintf('VE-%s-%04d', $fecha, $secuencia);
    }
}
