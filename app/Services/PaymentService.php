<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Cuota;
use Illuminate\Support\Collection;

/**
 * Servicio para la gestión de pagos y cuotas
 * 
 * Responsable de procesar pagos de cuotas, registrar transacciones,
 * aplicar pagos parciales y gestionar métodos de pago.
 */
class PaymentService
{
    /**
     * Obtiene todos los pagos con filtros opcionales
     * 
     * @param array $filters
     * @return Collection
     */
    public function getAllPayments(array $filters = []): Collection
    {
        $query = Pago::with(['cuota.credito', 'metodoPago']);

        if (isset($filters['fecha_desde'])) {
            $query->whereDate('fecha', '>=', $filters['fecha_desde']);
        }

        if (isset($filters['fecha_hasta'])) {
            $query->whereDate('fecha', '<=', $filters['fecha_hasta']);
        }

        return $query->orderBy('fecha', 'desc')->get();
    }

    /**
     * Obtiene un pago por su ID
     * 
     * @param int $id
     * @return Pago|null
     */
    public function getPaymentById(int $id): ?Pago
    {
        return Pago::with(['cuota.credito', 'metodoPago'])->find($id);
    }

    /**
     * Registra un pago para una cuota específica
     * 
     * @param int $installmentId
     * @param array $paymentData
     * @return Pago
     */
    public function registerPayment(int $installmentId, array $paymentData): Pago
    {
        $cuota = Cuota::findOrFail($installmentId);
        
        // Calcular mora si está vencida
        $mora = $this->calculateLateFee($installmentId);
        $totalAPagar = $cuota->monto_pendiente + $mora;

        // Actualizar mora en la cuota
        if ($mora > 0) {
            $cuota->update(['mora' => $mora]);
        }

        $pago = Pago::create([
            'cuota_id' => $installmentId,
            'monto' => $totalAPagar,
            'fecha' => $paymentData['fecha'] ?? now(),
            'metodo_pago_id' => $paymentData['metodo_pago_id'],
            'comprobante' => $paymentData['comprobante'] ?? null,
            'notas' => $paymentData['notas'] ?? null,
        ]);

        // Actualizar estado de la cuota
        $this->updateInstallmentStatus($installmentId, $totalAPagar);

        return $pago->load('cuota');
    }

    /**
     * Procesa un pago parcial para una cuota
     * 
     * @param int $installmentId
     * @param float $amount
     * @param array $paymentData
     * @return Pago
     */
    public function processPartialPayment(int $installmentId, float $amount, array $paymentData): Pago
    {
        $cuota = Cuota::findOrFail($installmentId);

        $pago = Pago::create([
            'cuota_id' => $installmentId,
            'monto' => $amount,
            'fecha' => $paymentData['fecha'] ?? now(),
            'metodo_pago_id' => $paymentData['metodo_pago_id'],
            'comprobante' => $paymentData['comprobante'] ?? null,
            'notas' => $paymentData['notas'] ?? 'Pago parcial',
        ]);

        $this->updateInstallmentStatus($installmentId, $amount);

        return $pago;
    }

    /**
     * Actualiza el estado de una cuota después de un pago
     * 
     * @param int $installmentId
     * @param float $paidAmount
     * @return Cuota
     */
    public function updateInstallmentStatus(int $installmentId, float $paidAmount): Cuota
    {
        $cuota = Cuota::with('credito')->findOrFail($installmentId);
        
        $cuota->monto_pagado += $paidAmount;
        $cuota->monto_pendiente = max(0, $cuota->monto + $cuota->mora - $cuota->monto_pagado);

        // Actualizar estado de la cuota
        if ($cuota->monto_pendiente <= 0.01) {
            $cuota->estado = 'pagada';
            $cuota->monto_pendiente = 0;
        }

        $cuota->save();

        // Actualizar el crédito
        $this->updateCreditProgress($cuota->credito_id);

        return $cuota->fresh();
    }

    /**
     * Aplica un pago a múltiples cuotas de un crédito
     * 
     * @param int $creditId
     * @param float $amount
     * @param array $paymentData
     * @return array
     */
    public function applyPaymentToCredit(int $creditId, float $amount, array $paymentData): array
    {
        $credito = \App\Models\Credito::with('cuotas')->findOrFail($creditId);
        $remainingAmount = $amount;
        $pagosProcesados = [];

        // Obtener cuotas pendientes ordenadas por fecha
        $cuotasPendientes = $credito->cuotas()
            ->whereIn('estado', ['pendiente', 'vencida'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        foreach ($cuotasPendientes as $cuota) {
            if ($remainingAmount <= 0) {
                break;
            }

            // Calcular mora si aplica
            $mora = $this->calculateLateFee($cuota->id);
            if ($mora > 0) {
                $cuota->update(['mora' => $mora]);
            }

            $totalCuota = $cuota->monto_pendiente + $mora;
            $montoPagar = min($remainingAmount, $totalCuota);

            // Registrar el pago
            $pago = Pago::create([
                'cuota_id' => $cuota->id,
                'monto' => $montoPagar,
                'fecha' => $paymentData['fecha'] ?? now(),
                'metodo_pago_id' => $paymentData['metodo_pago_id'],
                'comprobante' => $paymentData['comprobante'] ?? null,
                'notas' => $paymentData['notas'] ?? null,
            ]);

            $this->updateInstallmentStatus($cuota->id, $montoPagar);

            $pagosProcesados[] = [
                'cuota_id' => $cuota->id,
                'pago_id' => $pago->id,
                'monto' => $montoPagar,
            ];

            $remainingAmount -= $montoPagar;
        }

        return [
            'pagos_procesados' => $pagosProcesados,
            'monto_aplicado' => $amount - $remainingAmount,
            'sobrante' => $remainingAmount,
        ];
    }

    /**
     * Cancela o anula un pago registrado
     * 
     * @param int $paymentId
     * @param string $reason
     * @return bool
     */
    public function cancelPayment(int $paymentId, string $reason): bool
    {
        $pago = Pago::with('cuota')->findOrFail($paymentId);
        $cuota = $pago->cuota;

        // Revertir el pago
        $cuota->monto_pagado -= $pago->monto;
        $cuota->monto_pendiente += $pago->monto;
        $cuota->estado = $cuota->monto_pendiente > 0 ? 'pendiente' : 'pagada';
        $cuota->save();

        // Eliminar o marcar como anulado
        $pago->update(['notas' => ($pago->notas ?? '') . " | ANULADO: {$reason}"]);
        $pago->delete();

        // Actualizar crédito
        $this->updateCreditProgress($cuota->credito_id);

        return true;
    }

    /**
     * Obtiene los pagos realizados por un cliente
     * 
     * @param int $clientId
     * @return Collection
     */
    public function getPaymentsByClient(int $clientId): Collection
    {
        return Pago::with(['cuota.credito', 'metodoPago'])
            ->whereHas('cuota.credito.venta', function($q) use ($clientId) {
                $q->where('cliente_id', $clientId);
            })
            ->orderBy('fecha', 'desc')
            ->get();
    }

    /**
     * Obtiene los pagos procesados por un vendedor
     * 
     * @param int $vendorId
     * @return Collection
     */
    public function getPaymentsByVendor(int $vendorId): Collection
    {
        return Pago::with(['cuota.credito', 'metodoPago'])
            ->whereHas('cuota.credito.venta', function($q) use ($vendorId) {
                $q->where('vendedor_id', $vendorId);
            })
            ->orderBy('fecha', 'desc')
            ->get();
    }

    /**
     * Calcula el monto de mora para una cuota vencida
     * 
     * @param int $installmentId
     * @return float
     */
    public function calculateLateFee(int $installmentId): float
    {
        $cuota = Cuota::findOrFail($installmentId);
        
        if ($cuota->estado !== 'vencida' && now()->lessThanOrEqualTo($cuota->fecha_vencimiento)) {
            return 0;
        }

        // Actualizar estado si está vencida
        if (now()->greaterThan($cuota->fecha_vencimiento) && $cuota->estado !== 'pagada') {
            $cuota->update(['estado' => 'vencida']);
        }

        // Calcular días de atraso
        $fechaVencimiento = \Carbon\Carbon::parse($cuota->fecha_vencimiento);
        $diasAtraso = max(0, now()->diffInDays($fechaVencimiento, false) * -1);

        if ($diasAtraso <= 0) {
            return 0;
        }

        // Mora: 3% mensual proporcional a días
        $tasaMensual = 0.03;
        $tasaDiaria = $tasaMensual / 30;
        $mora = $cuota->monto_pendiente * $tasaDiaria * $diasAtraso;

        return round($mora, 2);
    }

    /**
     * Genera el recibo de pago en formato PDF
     * 
     * @param int $paymentId
     * @return string
     */
    public function generatePaymentReceipt(int $paymentId): string
    {
        // Implementación futura con librería PDF
        return "receipt_{$paymentId}.pdf";
    }

    /**
     * Obtiene estadísticas de pagos por periodo
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getPaymentStatistics(string $startDate, string $endDate): array
    {
        $pagos = Pago::whereBetween('fecha', [$startDate, $endDate])->get();

        return [
            'total_pagos' => $pagos->count(),
            'monto_total' => $pagos->sum('monto'),
            'promedio_pago' => $pagos->avg('monto'),
        ];
    }

    /**
     * Actualiza el progreso del crédito y cambia estado si está completado
     * 
     * @param int $creditId
     * @return void
     */
    protected function updateCreditProgress(int $creditId): void
    {
        $credito = \App\Models\Credito::with('cuotas')->findOrFail($creditId);
        
        $totalPagado = $credito->cuotas->sum('monto_pagado');
        $saldoPendiente = $credito->cuotas->sum('monto_pendiente');

        $credito->monto_pagado = $totalPagado;
        $credito->monto_pendiente = $saldoPendiente;

        // Si todas las cuotas están pagadas, marcar crédito como pagado
        $cuotasPendientes = $credito->cuotas->whereIn('estado', ['pendiente', 'vencida'])->count();
        
        if ($cuotasPendientes === 0 && $saldoPendiente <= 0.01) {
            $credito->estado = 'pagado';
        }

        $credito->save();
    }
}
