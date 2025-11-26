<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Servicio SIMULADO de PagoFácil Bolivia
 * 
 * Este servicio simula la funcionalidad de PagoFácil sin usar credenciales reales.
 * Más adelante se puede reemplazar por la API oficial sin cambiar el resto del código.
 */
class PagoFacilService
{
    private $mockMode = true; // Cambiar a false cuando uses la API real

    /**
     * Generar QR simulado para una venta online
     */
    public function generarQRVentaSimulado($ventaId, $monto, $glosa = null)
    {
        $transactionId = 'PF-VENTA-' . Str::uuid()->toString();
        
        // QR simulado (base64 de una imagen de prueba)
        $qrImage = $this->generarQRImagenSimulada($transactionId, $monto);

        Log::info("QR Simulado generado para venta #{$ventaId}", [
            'transaction_id' => $transactionId,
            'monto' => $monto,
            'glosa' => $glosa ?? "Venta #{$ventaId}"
        ]);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'qr_image' => $qrImage,
            'status' => 'pending',
            'monto' => $monto,
            'glosa' => $glosa ?? "Venta #{$ventaId}",
            'expiration' => now()->addHours(2)->toIso8601String()
        ];
    }

    /**
     * Generar QR simulado para pago de cuota
     */
    public function generarQRCuotaSimulado($cuotaId, $monto, $glosa = null)
    {
        $transactionId = 'PF-CUOTA-' . Str::uuid()->toString();
        
        $qrImage = $this->generarQRImagenSimulada($transactionId, $monto);

        Log::info("QR Simulado generado para cuota #{$cuotaId}", [
            'transaction_id' => $transactionId,
            'monto' => $monto,
            'glosa' => $glosa ?? "Pago Cuota #{$cuotaId}"
        ]);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'qr_image' => $qrImage,
            'status' => 'pending',
            'monto' => $monto,
            'glosa' => $glosa ?? "Pago Cuota #{$cuotaId}",
            'expiration' => now()->addHours(2)->toIso8601String()
        ];
    }

    /**
     * Simular confirmación de pago (usado en endpoint de pruebas)
     */
    public function simularConfirmacionPago($transactionId)
    {
        Log::info("Simulando confirmación de pago", ['transaction_id' => $transactionId]);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'status' => 'completed',
            'fecha_pago' => now()->toIso8601String(),
            'mensaje' => 'Pago simulado confirmado exitosamente'
        ];
    }

    /**
     * Validar webhook simulado
     */
    public function validarWebhookSimulado($data)
    {
        // En producción, aquí validarías la firma del webhook
        // Por ahora solo verificamos que tenga los campos necesarios
        
        $requiredFields = ['transaction_id', 'status'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                Log::warning("Webhook simulado inválido: falta campo {$field}");
                return false;
            }
        }

        return true;
    }

    /**
     * Generar imagen QR simulada (base64)
     * En producción, esto vendría de la API de PagoFácil
     */
    private function generarQRImagenSimulada($transactionId, $monto)
    {
        // QR simulado básico (1x1 pixel PNG en base64 con texto)
        // En producción, esto será una imagen QR real de PagoFácil
        
        $text = "PAGOFACIL SIMULADO\nID: {$transactionId}\nMonto: Bs. " . number_format($monto, 2);
        
        // Retornar un placeholder SVG como QR simulado
        $svg = '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
            <rect width="200" height="200" fill="#f0f0f0"/>
            <text x="100" y="80" font-family="Arial" font-size="12" text-anchor="middle" fill="#333">
                PAGO SIMULADO
            </text>
            <text x="100" y="100" font-family="monospace" font-size="8" text-anchor="middle" fill="#666">
                ' . substr($transactionId, 0, 20) . '
            </text>
            <text x="100" y="120" font-family="Arial" font-size="14" text-anchor="middle" fill="#000" font-weight="bold">
                Bs. ' . number_format($monto, 2) . '
            </text>
            <text x="100" y="140" font-family="Arial" font-size="10" text-anchor="middle" fill="#999">
                Escanea para pagar
            </text>
            <rect x="60" y="150" width="80" height="30" fill="#4CAF50" rx="4"/>
            <text x="100" y="170" font-family="Arial" font-size="10" text-anchor="middle" fill="#fff">
                QR SIMULADO
            </text>
        </svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Verificar estado de pago (consulta a la API)
     * En producción, consultaría el estado real en PagoFácil
     */
    public function verificarEstadoPago($transactionId)
    {
        Log::info("Verificando estado de pago", ['transaction_id' => $transactionId]);

        // En modo simulado, retornamos estado pendiente por defecto
        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'status' => 'pending',
            'mensaje' => 'Pago pendiente de confirmación (simulado)'
        ];
    }

    /**
     * Determinar si es una transacción de venta o cuota
     */
    public function getTipoTransaccion($transactionId)
    {
        if (Str::startsWith($transactionId, 'PF-VENTA-')) {
            return 'venta';
        } elseif (Str::startsWith($transactionId, 'PF-CUOTA-')) {
            return 'cuota';
        }
        
        return 'unknown';
    }
}
