<?php

namespace App\Services;

use App\Models\Credito;
use App\Models\Cuota;
use Illuminate\Support\Collection;

/**
 * Servicio para la gestión de créditos y cuotas
 * 
 * Responsable de crear créditos, calcular planes de pago,
 * gestionar cuotas y controlar el estado de los créditos.
 */
class CreditService
{
    /**
     * Obtiene todos los créditos con filtros opcionales
     * 
     * @param array $filters
     * @return Collection
     */
    public function getAllCredits(array $filters = []): Collection
    {
        $query = Credito::with(['venta', 'cuotas']);

        if (isset($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (isset($filters['cliente_id'])) {
            $query->whereHas('venta', function($q) use ($filters) {
                $q->where('cliente_id', $filters['cliente_id']);
            });
        }

        return $query->get();
    }

    /**
     * Obtiene un crédito por su ID con cuotas relacionadas
     * 
     * @param int $id
     * @return Credito|null
     */
    public function getCreditById(int $id): ?Credito
    {
        return Credito::with(['venta', 'cuotas.pagos'])->find($id);
    }

    /**
     * Crea un nuevo crédito para una venta
     * 
     * @param int $saleId
     * @param array $creditData
     * @return Credito
     */
    public function createCredit(int $saleId, array $creditData): Credito
    {
        $venta = \App\Models\Venta::findOrFail($saleId);
        
        // Validar elegibilidad del cliente
        if (!$this->checkCreditEligibility($venta->cliente_id, $venta->total)) {
            throw new \Exception('El cliente no es elegible para crédito. Tiene cuotas vencidas o crédito activo pendiente.');
        }

        // Crear el crédito
        $credito = Credito::create([
            'venta_id' => $saleId,
            'monto_total' => $venta->total,
            'monto_pagado' => 0,
            'monto_pendiente' => $venta->total,
            'numero_cuotas' => $creditData['numero_cuotas'],
            'fecha_inicio' => $creditData['fecha_inicio'] ?? now(),
            'estado' => 'activo',
        ]);

        // Generar cuotas automáticamente
        $installmentPlan = $this->calculateInstallmentPlan(
            $venta->total,
            $creditData['numero_cuotas'],
            0, // Sin interés
            $creditData['fecha_inicio'] ?? now()->toDateString()
        );

        $this->generateInstallments($credito->id, $installmentPlan);

        return $credito->load('cuotas');
    }

    /**
     * Calcula el plan de cuotas para un crédito
     * 
     * @param float $totalAmount
     * @param int $numberOfInstallments
     * @param float $interestRate
     * @param string $startDate
     * @return array
     */
    public function calculateInstallmentPlan(float $totalAmount, int $numberOfInstallments, float $interestRate, string $startDate): array
    {
        // División en cuotas iguales sin interés
        $installmentAmount = round($totalAmount / $numberOfInstallments, 2);
        
        // Ajustar última cuota para compensar redondeos
        $totalCalculated = $installmentAmount * ($numberOfInstallments - 1);
        $lastInstallmentAmount = round($totalAmount - $totalCalculated, 2);

        $plan = [];
        $currentDate = \Carbon\Carbon::parse($startDate);

        for ($i = 1; $i <= $numberOfInstallments; $i++) {
            $amount = ($i === $numberOfInstallments) ? $lastInstallmentAmount : $installmentAmount;
            
            $plan[] = [
                'numero_cuota' => $i,
                'monto' => $amount,
                'fecha_vencimiento' => $currentDate->copy()->addMonths($i)->toDateString(),
            ];
        }

        return $plan;
    }

    /**
     * Genera las cuotas para un crédito
     * 
     * @param int $creditId
     * @param array $installmentPlan
     * @return Collection
     */
    public function generateInstallments(int $creditId, array $installmentPlan): Collection
    {
        $cuotas = collect();

        foreach ($installmentPlan as $plan) {
            $cuota = Cuota::create([
                'credito_id' => $creditId,
                'numero_cuota' => $plan['numero_cuota'],
                'monto' => $plan['monto'],
                'monto_pagado' => 0,
                'monto_pendiente' => $plan['monto'],
                'fecha_vencimiento' => $plan['fecha_vencimiento'],
                'estado' => 'pendiente',
                'mora' => 0,
            ]);

            $cuotas->push($cuota);
        }

        return $cuotas;
    }

    /**
     * Obtiene los créditos de un cliente específico
     * 
     * @param int $clientId
     * @return Collection
     */
    public function getCreditsByClient(int $clientId): Collection
    {
        return Credito::with(['venta', 'cuotas'])
            ->whereHas('venta', function($q) use ($clientId) {
                $q->where('cliente_id', $clientId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Actualiza el estado de un crédito
     * 
     * @param int $id
     * @param string $newStatus
     * @return Credito
     */
    public function updateCreditStatus(int $id, string $newStatus): Credito
    {
        $credito = Credito::findOrFail($id);
        $credito->update(['estado' => $newStatus]);
        
        return $credito;
    }

    /**
     * Obtiene créditos vencidos o por vencer
     * 
     * @param int $daysThreshold
     * @return Collection
     */
    public function getOverdueCredits(int $daysThreshold = 0): Collection
    {
        return Credito::with(['venta', 'cuotas'])
            ->where('estado', 'activo')
            ->whereHas('cuotas', function($q) use ($daysThreshold) {
                $q->where('estado', 'vencida')
                  ->orWhere(function($subQ) use ($daysThreshold) {
                      $subQ->where('estado', 'pendiente')
                           ->whereDate('fecha_vencimiento', '<=', now()->addDays($daysThreshold));
                  });
            })
            ->get();
    }

    /**
     * Calcula el saldo pendiente de un crédito
     * 
     * @param int $creditId
     * @return float
     */
    public function calculateRemainingBalance(int $creditId): float
    {
        $credito = Credito::with('cuotas')->findOrFail($creditId);
        
        return $credito->cuotas->sum('monto_pendiente');
    }

    /**
     * Obtiene el historial de pagos de un crédito
     * 
     * @param int $creditId
     * @return Collection
     */
    public function getCreditPaymentHistory(int $creditId): Collection
    {
        return \App\Models\Pago::with(['cuota', 'metodoPago'])
            ->whereHas('cuota', function($q) use ($creditId) {
                $q->where('credito_id', $creditId);
            })
            ->orderBy('fecha', 'desc')
            ->get();
    }

    /**
     * Verifica si un cliente puede acceder a más crédito
     * 
     * @param int $clientId
     * @param float $requestedAmount
     * @return bool
     */
    public function checkCreditEligibility(int $clientId, float $requestedAmount): bool
    {
        // Verificar si tiene créditos activos
        $activeCredits = Credito::whereHas('venta', function($q) use ($clientId) {
            $q->where('cliente_id', $clientId);
        })->where('estado', 'activo')->count();

        if ($activeCredits > 0) {
            return false;
        }

        // Verificar si tiene cuotas vencidas
        $overdueCuotas = Cuota::whereHas('credito.venta', function($q) use ($clientId) {
            $q->where('cliente_id', $clientId);
        })->where('estado', 'vencida')->count();

        if ($overdueCuotas > 0) {
            return false;
        }

        return true;
    }
}
