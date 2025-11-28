<?php

namespace App\Http\Controllers;

use App\Models\Cuota;
use App\Models\Pago;
use App\Models\MetodoPago;
use App\Services\PagoFacilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para gestionar pagos de cuotas con QR (PagoFácil simulado)
 * NO modifica la funcionalidad existente de pagos manuales
 */
class PagoCuotaController extends Controller
{
    public function __construct(
        protected PagoFacilService $pagoFacilService
    ) {}

    /**
     * Generar QR para pago de cuota (SIMULADO)
     */
    public function generarQRCuota(Request $request, $cuotaId)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01'
        ]);

        try {
            $cuota = Cuota::with('credito.venta')->findOrFail($cuotaId);
            
            // Validar que la cuota pertenezca al usuario autenticado
            if ($cuota->credito->venta->user_id !== auth()->id() && !auth()->user()->esAdministrador()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            // Validar que el monto no exceda lo pendiente
            // El modelo `Cuota` usa campos `monto` y `monto_pendiente`.
            $montoPendiente = $cuota->monto_pendiente ?? ($cuota->monto - $cuota->monto_pagado);
            if ($request->monto > $montoPendiente) {
                return response()->json([
                    'error' => 'El monto excede lo pendiente de la cuota',
                    'monto_pendiente' => $montoPendiente
                ], 422);
            }

            DB::beginTransaction();

            // Obtener método de pago QR
            $metodoPagoQR = MetodoPago::where('nombre', 'QR')->first();
            if (!$metodoPagoQR) {
                throw new \Exception('Método de pago QR no configurado');
            }

            // Crear registro de pago en estado pendiente
            // Asegurarse de incluir la fecha, la columna es NOT NULL en la BD
            $pago = Pago::create([
                'cuota_id' => $cuota->id,
                'monto' => $request->monto,
                'metodo_pago_id' => $metodoPagoQR->id,
                'fecha' => now(),
                'pago_facil_status' => 'pending'
            ]);

            // Generar QR simulado
            $glosa = "Pago Cuota #{$cuota->numero_cuota} - Crédito #{$cuota->credito_id}";
            $qrData = $this->pagoFacilService->generarQRCuotaSimulado(
                $cuota->id,
                $request->monto,
                $glosa
            );

            // Actualizar pago con datos del QR
            $pago->update([
                'pago_facil_transaction_id' => $qrData['transaction_id'],
                'pago_facil_payment_number' => $qrData['payment_number'] ?? null,
                'pago_facil_qr_image' => $qrData['qr_image'],
                'pago_facil_status' => 'pending'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'pago_id' => $pago->id,
                'transaction_id' => $qrData['transaction_id'],
                'qr_image' => $qrData['qr_image'],
                'monto' => $request->monto,
                'expiration' => $qrData['expiration'],
                'message' => 'QR generado exitosamente. Escanea para pagar.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al generar QR para cuota', [
                'cuota_id' => $cuotaId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Error al generar QR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook simulado para confirmar pago de cuota
     */
    public function webhookCuotaSimulado(Request $request)
    {
        try {
            // Validar webhook
            if (!$this->pagoFacilService->validarWebhookSimulado($request->all())) {
                return response()->json(['error' => 'Webhook inválido'], 400);
            }

            $transactionId = $request->input('transaction_id');
            $status = $request->input('status');

            // Buscar el pago por transaction_id
            $pago = Pago::where('pago_facil_transaction_id', $transactionId)->first();

            if (!$pago) {
                Log::warning('Pago no encontrado para transaction_id', ['transaction_id' => $transactionId]);
                return response()->json(['error' => 'Pago no encontrado'], 404);
            }

            // Si ya está completado, no procesar de nuevo
            if ($pago->pago_facil_status === 'completed') {
                return response()->json(['message' => 'Pago ya procesado'], 200);
            }

            DB::beginTransaction();

            if ($status === 'completed') {
                // Actualizar estado del pago
                $pago->update([
                    'pago_facil_status' => 'completed',
                    'pago_facil_raw_response' => json_encode($request->all())
                ]);

<<<<<<< HEAD
=======
<<<<<<< HEAD
                // Actualizar cuota
                $cuota = $pago->cuotaCredito;
                $cuota->monto_pagado += $pago->monto;
                
                // Si la cuota está completamente pagada, marcarla como pagada
                if ($cuota->monto_pagado >= $cuota->monto_cuota) {
                    $cuota->estado = 'pagado';
                    $cuota->fecha_pago = now();
=======
>>>>>>> limber
                // Actualizar cuota (actualizar columnas específicas para evitar escribir columnas inexistentes)
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
<<<<<<< HEAD
=======
>>>>>>> a71c191 (mora e interes en los creditos funcionando)
>>>>>>> limber
                }

                // Verificar si todas las cuotas del crédito están pagadas
                $credito = $cuota->credito;
                $todasPagadas = $credito->cuotas()->where('estado', '!=', 'pagado')->count() === 0;

                if ($todasPagadas) {
                    $credito->update(['estado' => 'pagado']);
                    
                    Log::info('Crédito completamente pagado', ['credito_id' => $credito->id]);
                }

                DB::commit();

                Log::info('Pago de cuota confirmado via webhook simulado', [
                    'pago_id' => $pago->id,
                    'cuota_id' => $cuota->id,
                    'monto' => $pago->monto
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Pago confirmado exitosamente',
                    'pago_id' => $pago->id
                ]);

            } else {
                // Pago fallido
                $pago->update([
                    'pago_facil_status' => 'failed',
                    'pago_facil_raw_response' => json_encode($request->all())
                ]);

                DB::commit();

                return response()->json([
                    'success' => false,
                    'message' => 'Pago fallido'
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en webhook de cuota simulado', [
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
            'monto' => $request->input('monto', 0),
            'simulated' => true
        ];

        // Llamar al webhook simulado
        $webhookRequest = Request::create('/webhook/pagofacil-simulado/cuota', 'POST', $webhookData);
        return $this->webhookCuotaSimulado($webhookRequest);
    }

    /**
     * Verificar estado de un pago QR
     */
    public function verificarEstadoPago($pagoId)
    {
        try {
            $pago = Pago::findOrFail($pagoId);

            // Validar que pertenezca al usuario autenticado
            if ($pago->cuota && $pago->cuota->credito->venta->user_id !== auth()->id() && !auth()->user()->esAdministrador()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            return response()->json([
                'success' => true,
                'pago_id' => $pago->id,
                'transaction_id' => $pago->pago_facil_transaction_id,
                'status' => $pago->pago_facil_status,
                'monto' => $pago->monto,
                'created_at' => $pago->created_at,
                'updated_at' => $pago->updated_at
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al verificar pago: ' . $e->getMessage()
            ], 500);
        }
    }
}
