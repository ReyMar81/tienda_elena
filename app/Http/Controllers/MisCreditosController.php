<?php

namespace App\Http\Controllers;

use App\Models\Credito;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MisCreditosController extends Controller
{
    /**
     * Muestra los créditos del cliente autenticado
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Filtros
        $estado = $request->input('estado', '');
        $search = $request->input('search', '');

        $query = Credito::with(['venta', 'venta.user', 'cuotas'])
            ->whereHas('venta', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('venta', function($ventaQuery) use ($search) {
                    $ventaQuery->where('numero_venta', 'like', "%{$search}%");
                });
            });
        }

        $creditos = $query->orderBy('created_at', 'desc')->paginate(10);

        return Inertia::render('MisCreditos/Index', [
            'creditos' => $creditos,
            'filtros' => $request->only(['estado', 'search'])
        ]);
    }

    /**
     * Detalle de un crédito del cliente autenticado
     */
    public function show($id)
    {
        $user = auth()->user();

        $credito = Credito::with([
            'venta.user',
            'venta.detalles.producto',
            'cuotas.pagos.metodoPago',
            'cuotas' => function($q) {
                $q->orderBy('numero_cuota');
            }
        ])->whereHas('venta', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($id);

        return Inertia::render('MisCreditos/Show', [
            'credito' => $credito,
        ]);
    }

    /**
     * Registrar pago de una cuota por el cliente autenticado
     */
    public function registrarPago(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'cuota_id' => 'required|exists:cuotas,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
        ]);

        $cuota = \App\Models\Cuota::with('credito.venta')->findOrFail($request->cuota_id);

        // Verificar que la cuota pertenezca al usuario autenticado
        if ($cuota->credito->venta->user_id !== $user->id) {
            abort(403, 'No autorizado para pagar esta cuota.');
        }

        if ($request->monto > $cuota->monto_pendiente) {
            return back()->withErrors([
                'monto' => 'El monto no puede ser mayor al monto pendiente de la cuota (Bs. ' . number_format($cuota->monto_pendiente, 2) . ')'
            ]);
        }

        \DB::beginTransaction();
        try {
            // Método de pago QR (id fijo o buscar por nombre)
            $metodoQR = \App\Models\MetodoPago::where('nombre', 'QR')->first();
            if (!$metodoQR) {
                throw new \Exception('Método de pago QR no disponible.');
            }

            $pago = \App\Models\Pago::create([
                'cuota_id' => $cuota->id,
                'metodo_pago_id' => $metodoQR->id,
                'monto' => $request->monto,
                'recargo_extra' => 0,
                'interes_mora_cobrado' => 0,
                'fecha' => $request->fecha,
            ]);

            // Actualizar la cuota
            $cuota->monto_pagado += $request->monto;
            $cuota->monto_pendiente -= $request->monto;
            if ($cuota->monto_pendiente <= 0.01) {
                $cuota->estado = 'pagada';
                $cuota->monto_pendiente = 0;
            }
            $cuota->save();

            // Actualizar el crédito
            $credito = $cuota->credito;
            $credito->monto_pagado += $request->monto;
            $credito->monto_pendiente -= $request->monto;
            if ($credito->monto_pendiente <= 0.01) {
                $credito->estado = 'pagado';
                $credito->monto_pendiente = 0;
                $credito->dias_mora = 0;
            }
            $credito->save();

            \DB::commit();
            return redirect()->back()->with('success', 'Pago registrado exitosamente.');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al registrar pago (cliente): ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al registrar el pago: ' . $e->getMessage()]);
        }
    }
}
