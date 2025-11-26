<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePagoRequest;
use App\Models\Credito;
use App\Models\Cuota;
use App\Models\Pago;
use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CreditoController extends Controller
{
    /**
     * Listado de créditos con filtros
     */
    public function index(Request $request)
    {
        $query = Credito::with(['venta', 'venta.user', 'cuotas']);

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Búsqueda por cliente o número de venta
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('venta.user', function($userQuery) use ($search) {
                    $userQuery->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('ci', 'like', "%{$search}%");
                })->orWhereHas('venta', function($ventaQuery) use ($search) {
                    $ventaQuery->where('numero_venta', 'like', "%{$search}%");
                });
            });
        }

        $creditos = $query->orderBy('created_at', 'desc')->paginate(15);

        // Indicadores
        $indicadores = [
            'total_creditos' => Credito::count(),
            'total_pendiente' => Credito::sum('monto_pendiente'),
            'total_mora' => Credito::where('dias_mora', '>', 0)->count(),
            'monto_mora' => Credito::where('dias_mora', '>', 0)->sum('monto_pendiente'),
        ];

        $metodosPago = MetodoPago::all();

        return Inertia::render('Creditos/Index', [
            'creditos' => $creditos,
            'indicadores' => $indicadores,
            'filtros' => $request->only(['estado', 'search']),
            'metodosPago' => $metodosPago,
        ]);
    }

    /**
     * Detalle completo de un crédito
     */
    public function show($id)
    {
        $credito = Credito::with([
            'venta.user',
            'venta.detalles.producto',
            'cuotas.pagos.metodoPago',
            'cuotas' => function($q) {
                $q->orderBy('numero_cuota');
            }
        ])->findOrFail($id);

        $metodosPago = MetodoPago::all();

        return Inertia::render('Creditos/Show', [
            'credito' => $credito,
            'metodosPago' => $metodosPago,
        ]);
    }

    /**
     * Registrar pago de una cuota
     */
    public function registrarPago(StorePagoRequest $request)
    {
        try {
            DB::beginTransaction();

            $cuota = Cuota::with('credito')->findOrFail($request->cuota_id);

            // Validar que el monto no exceda el monto pendiente
            if ($request->monto > $cuota->monto_pendiente) {
                DB::rollBack();
                return back()->withErrors([
                    'monto' => 'El monto no puede ser mayor al monto pendiente de la cuota (Bs. ' . number_format($cuota->monto_pendiente, 2) . ')'
                ]);
            }

            // Crear el pago
            $pago = Pago::create([
                'cuota_id' => $cuota->id,
                'metodo_pago_id' => $request->metodo_pago_id,
                'monto' => $request->monto,
                'recargo_extra' => 0,
                'interes_mora_cobrado' => 0,
                'fecha' => $request->fecha,
            ]);

            // Actualizar la cuota
            $cuota->monto_pagado += $request->monto;
            $cuota->monto_pendiente -= $request->monto;

            // Si se pagó completamente, cambiar estado
            if ($cuota->monto_pendiente <= 0.01) {
                $cuota->estado = 'pagado';
                $cuota->monto_pendiente = 0;
            }
            $cuota->save();

            // Actualizar el crédito
            $credito = $cuota->credito;
            $credito->monto_pagado += $request->monto;
            $credito->monto_pendiente -= $request->monto;

            // Si se pagó completamente el crédito, cambiar estado
            if ($credito->monto_pendiente <= 0.01) {
                $credito->estado = 'pagado';
                $credito->monto_pendiente = 0;
                $credito->dias_mora = 0;
            }
            $credito->save();

            // Actualizar estados de mora
            $this->actualizarMora($credito);

            DB::commit();

            return redirect()->route('creditos.show', $credito->id)
                ->with('success', 'Pago de Bs. ' . number_format($request->monto, 2) . ' registrado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al registrar pago: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al registrar el pago: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualizar estados y días de mora
     */
    public function actualizarEstados()
    {
        try {
            $creditosActualizados = 0;

            // Obtener todos los créditos pendientes
            $creditos = Credito::where('estado', 'pendiente')->get();

            foreach ($creditos as $credito) {
                $this->actualizarMora($credito);
                $creditosActualizados++;
            }

            return response()->json([
                'success' => true,
                'message' => "{$creditosActualizados} créditos actualizados",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estados: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reporte de créditos en mora
     */
    public function reporteMora()
    {
        $creditosMora = Credito::with(['venta.user', 'cuotas' => function($q) {
            $q->where('estado', 'vencida')->orderBy('fecha_vencimiento');
        }])
        ->where('dias_mora', '>', 0)
        ->orderByDesc('dias_mora')
        ->get();

        return Inertia::render('Creditos/ReporteMora', [
            'creditosMora' => $creditosMora,
        ]);
    }

    /**
     * Actualizar días de mora de un crédito y sus cuotas
     */
    private function actualizarMora(Credito $credito)
    {
        $diasMoraMaximo = 0;

        foreach ($credito->cuotas as $cuota) {
            if ($cuota->estado === 'pendiente' && $cuota->fecha_vencimiento < now()) {
                // Actualizar estado a vencida
                $cuota->estado = 'vencida';
                
                // Calcular días de mora
                $diasMora = now()->diffInDays($cuota->fecha_vencimiento);
                $cuota->dias_mora = $diasMora;
                $cuota->save();

                // Guardar el máximo de días de mora
                if ($diasMora > $diasMoraMaximo) {
                    $diasMoraMaximo = $diasMora;
                }
            }
        }

        // Actualizar días de mora del crédito
        $credito->dias_mora = $diasMoraMaximo;
        
        // Si tiene mora, cambiar estado
        if ($diasMoraMaximo > 0 && $credito->estado === 'pendiente') {
            $credito->estado = 'vencido';
        }
        
        $credito->save();
    }
}
